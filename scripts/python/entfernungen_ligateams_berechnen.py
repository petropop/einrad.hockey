import mysql.connector
from tqdm import tqdm


def get_distanz_luftlinie(ort_1, ort_2):

    #https: // www.python - forum.de / viewtopic.php?t = 19980
    from math import sin, cos, sqrt, asin, radians

    lat1 = radians(ort_1["LAT"])
    long1 = radians(ort_1["Lon"])

    lat2 = radians(ort_2["LAT"])
    long2 = radians(ort_2["Lon"])

    diff_lat = abs(lat2 - lat1)
    diff_long = abs(long2 - long1)

    r_equat = 6378.1
    r_polar = 6356.7
    average_lat = (lat1 + lat2) / 2
    ecc = sqrt(1 - (r_polar**2 / r_equat**2))

    r1 = (r_equat * (1 - ecc**2)) / (1 - ecc**2 * sin(average_lat)**2)**(1.5)
    r2 = r_equat / (sqrt(1 - ecc**2 * sin(average_lat)**2))
    r_average = (r1 * (diff_lat / (diff_lat + diff_long))) + (r2 * (diff_long / (diff_lat + diff_long)))

    res = sin(diff_lat / 2)**2 + cos(lat1) * cos(lat2) * sin(diff_long / 2)**2
    c = 2 * asin(min(1, sqrt(res)))
    distance = r_average * c
    return distance

if __name__=="__main__":
    #db_path = r'C:\xampp\htdocs\einrad.hockey\'
    #output_db_path = r'C:\code\einrad_hockey\python\dummy-db_distances.sql'

    mydb = mysql.connector.connect(
        host="127.0.0.1",
        user='root',
        password='',
        database="dummy-db"
    )

    print(mydb)

    mycursor = mydb.cursor()

    sql = r"SELECT tl.team_id, teamname, (SELECT p.Lon FROM `plz`p WHERE p.plz=td.plz) AS Lon, (SELECT p.LAT FROM " \
          r"`plz`p WHERE p.plz=td.plz) AS LAT FROM `teams_details` td LEFT JOIN `teams_liga`tl ON td.team_id = tl.team_id"
    mycursor.execute(sql)

    myresult = mycursor.fetchall()

    for x in myresult:
        print(x)
    results = []
    for x in myresult:
        for y in myresult:
            ort_1 = {"LAT": x[3],
                     "Lon": x[2]
            }
            ort_2 = {"LAT": y[3],
                     "Lon": y[2]
            }
            if x[2] is None or y[2] is None:
                print(f"{x[1]}<->{y[1]}: Missing km")
                #results.append([x[0], y[0], None])
                continue

            if x[2] == y[2] and x[3] == y[3]:
                print(f"{x[1]}<->{y[1]}: 0 km")
                results.append([x[0],x[1], y[0],y[1], 0.0])
            else:
                d_luftlinie = get_distanz_luftlinie(ort_1, ort_2)
                print(f"{x[1]}<->{y[1]}: {d_luftlinie} km")
                results.append([x[0],x[1], y[0],y[1], d_luftlinie])
    mycursor.execute("SHOW TABLES")
    myresult = mycursor.fetchall()
    table_created = bool(max(['entfernungen' == table_key[0] for table_key in myresult]))
    if not table_created:
        sql_table = "CREATE TABLE `entfernungen` (`team_id_a` int(11) NOT NULL,`teamname_a` varchar(255) NOT NULL," \
                    "`team_id_b` int(11) NOT NULL,`teamname_b` varchar(255) NOT NULL, entfernung double NOT NULL)" \
                    "ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        mycursor.execute(sql_table)
    mycursor = mydb.cursor()
    for x_id, x_name, y_id, y_name, d in tqdm(results):
        sql = "INSERT INTO entfernungen (team_id_a, teamname_a, team_id_b, teamname_b, entfernung) VALUES (%s, %s, %s, %s, %s)"
        val = (x_id, x_name, y_id, y_name, d)
        #print((x_id, x_name, y_id, y_name, d))
        mycursor.execute(sql, val)
        mydb.commit()
    A=1