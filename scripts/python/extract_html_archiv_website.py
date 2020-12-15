import csv
import os
from html.parser import HTMLParser
from bs4 import BeautifulSoup
import numpy as np
from pathlib import Path
from dateutil.parser import parse
import re
from tqdm import tqdm

DEBUG = False

def get_all_files_with_extension(folder, extensions):
    if isinstance(extensions, str):
        extensions = []
    file_list = []
    for ext in extensions:
        file_list = file_list+list(Path(folder).rglob('*'+ext))
    return file_list

def extract_turnier_meta(archiv_path):

    if re.search('[0-9]', os.path.basename(archiv_path)) is None:
        if DEBUG:
            print(archiv_path)
            return ['No result', archiv_path, 'No number in html path - File excluded']
        else:
            return None

    with open(archiv_path) as fp:
        soup = BeautifulSoup(fp, 'html.parser')
    table = soup.find_all('table')
    if '1997' in str(archiv_path) or '1998' in str(archiv_path) or '1999' in str(archiv_path):
        meta_table_id = 0
        b = soup.find_all('b')
        meta = b[1:]
    else:
        meta_table_id = 0
        meta = table[meta_table_id].find_all('tr')
    if len(table)< meta_table_id+1:
        if DEBUG:
            print(archiv_path)
            return ['No result', archiv_path, 'not enough table']
        else:
            return None
    if 'Abschlu' in meta[0].text:
        if DEBUG:
            print(archiv_path)
            return ['Abschlussturnier', archiv_path, 'excluded']
        else:
            return None
    match = re.search('([0-9a-zA-ZäöüÄÖÜß]+)(?:,|)(\s|)([0-9]|[0-3][0-9])\.([0-9]|[0-1][0-9])\.(19[0-9][0-9]|20[0-9][0-9]|[0,9][0-9])', meta[0].text)
    if match is None:
        if DEBUG:
            print(archiv_path)
            return ['No result', archiv_path, 'date not found']
        else:
            return None
    ort = match.group(1)
    day = match.group(3)
    month = match.group(4)
    year = match.group(5)
    if year[0] == '9':
        year = '19'+year
    # Die zweite Tabelle (table[1]) gibt den Rang der Teams im Turnier und deren Wertigkeit
    tr = table[meta_table_id+1].find_all('tr')
    td = []
    for tr_el in tr:
        td_liste = tr_el.find_all('td')
        if len(td_liste) in [1, 2] and 'Gruppe' not in td_liste[0].text:
            td = td + [' '.join(td_el.text.split()) for td_el in td_liste]
    team_turnier_rang_list = []
    team_name_list = []
    wertigkeit_list = []
    count = 0
    for team_str in td:
        count = count+1
        team_str_splitted = team_str.split()
        wertigkeit = ''
        if len(team_str_splitted)>0:
            mit_wertigkeit = re.search('\(([0-9]{1,3})\)', team_str_splitted[-1])
            if mit_wertigkeit:
                team_str_splitted = team_str_splitted[:-1]
                wertigkeit = mit_wertigkeit.group(1)
        wertigkeit_list.append(wertigkeit)
        try:
            team_turnier_rang_list.append(int(team_str_splitted[0]))
            #[0-9]{1,3}= [0-999]; {1,3}={min,max} is a multiplier
            team_name_list.append(' '.join(team_str_splitted[1:]))
        except:
            team_turnier_rang_list.append(count)
            team_name_list.append(' '.join(team_str_splitted))
    sort_idx = np.argsort(team_turnier_rang_list)
    team_turnier_rang_list = list(np.array(team_turnier_rang_list)[sort_idx])
    team_name_list = list(np.array(team_name_list)[sort_idx])
    wertigkeit_list = list(np.array(wertigkeit_list)[sort_idx])
    team_rang_name = list(np.reshape(np.array([team_turnier_rang_list, team_name_list, wertigkeit_list]).transpose(), -1))

    return [day, month, year, ort]+team_rang_name


if __name__=="__main__":

    archiv_folder =r"C:\Users\Tobias\Documents\Einrad\Einradhockeyliga_Website\liga\liga"
    #archiv_path = r"C:\Users\Tobias\Documents\Einrad\Einradhockeyliga yx_Website\liga\liga\2000\Bo2608e.htm"
    csv_path = r'C:\code\einrad_hockey\scripts\python\turniere_mit_rang.csv'

    file_list = get_all_files_with_extension(archiv_folder, ['.htm', '.html'])

    turniere_meta = []
    for archiv_path in tqdm(file_list):
        meta = extract_turnier_meta(archiv_path)
        if meta is not None:
            turniere_meta.append(meta)
    if DEBUG:
        with open(csv_path, 'w', newline='') as myfile:
            wr = csv.writer(myfile, delimiter=';', quoting=csv.QUOTE_ALL)
            for entry in turniere_meta:
                wr.writerow(entry)
        exit(1)


    #sort entries by date
    indices = np.argsort([10000*int(entry[2]) + 100*int(entry[1]) + int(entry[0]) for entry in turniere_meta])
    sorted_turniere_meta = list(np.array(turniere_meta)[indices])

    with open(csv_path, 'w', newline='') as myfile:
        wr = csv.writer(myfile, delimiter=';', quoting=csv.QUOTE_ALL)
        current_year = 1997
        turnier_id = 1
        for entry in sorted_turniere_meta:
            if current_year < int(entry[2]):
                turnier_id = 1
                current_year = int(entry[2])
            wr.writerow([entry[2]+str(turnier_id)]+entry)
            turnier_id = turnier_id + 1

    A=1
    # with open(archiv_path, newline='') as csvfile:
    #     reader = csv.reader(csvfile)
    #     for row in tqdm(reader):
    #         val = row[0].split(";")
    #         # (turnier_id, spiel_id, team_id_a, team_id_b, schiri_team_id_a, schiri_team_id_b, tore_a, tore_b
    #         spiel = (val[0], val[1], val[5], val[7], ?, ?, val[9], val[10], val[11], val[12])
    #         spiele_liste.append(spiel)
    #
    #         print(len(row[0].split(";")))
    #
    #         count = count +1
    #         print(count)