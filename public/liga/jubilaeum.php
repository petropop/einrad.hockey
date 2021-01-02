<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
$titel = "25 Jahre | Deutsche Einradhockeyliga";
$content = "Spielerprofile für das 25. Jubiläum der Deutschen Einradhockeyliga";
include '../../templates/header.tmp.php';
?>

<div class="w3-content w3-display-container">
    <img class="slideshow1" src="../bilder/spielerprofile/Adrian.jpg" style="width:100%">
    <img class="slideshow1" src="../bilder/spielerprofile/Adrian2.jpg" style="width:100%">
    <img class="slideshow1" src="../bilder/spielerprofile/Adrian6.jpg" style="width:100%">
    <img class="slideshow1" src="../bilder/spielerprofile/Adrian7.jpg" style="width:100%">

    <button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1, 0)">&#10094;</button>
    <button class="w3-button w3-black w3-display-right" onclick="plusDivs(1, 0)">&#10095;</button>
</div>

<div class="w3-content w3-display-container">
    <img class="slideshow2" src="../bilder/spielerprofile/Adrian.jpg" style="width:100%">
    <img class="slideshow2" src="../bilder/spielerprofile/Adrian2.jpg" style="width:100%">
    <img class="slideshow2" src="../bilder/spielerprofile/Adrian6.jpg" style="width:100%">
    <img class="slideshow2" src="../bilder/spielerprofile/Adrian7.jpg" style="width:100%">

    <button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1, 1)">&#10094;</button>
    <button class="w3-button w3-black w3-display-right" onclick="plusDivs(1, 1)">&#10095;</button>
</div>

<script>
var slideIndex = [1,1];
var slideId = ["slideshow1","slideshow2"];
showDivs(1,0);
showDivs(1,1);

function plusDivs(n, no) {
  showDivs(slideIndex[no] += n, no);
}

function showDivs(n, no) {
  var i;
  var x = document.getElementsByClassName(slideId[no]);
  if (n > x.length) {slideIndex[no] = 1}
  if (n < 1) {slideIndex[no] = x.length}
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  x[slideIndex[no]-1].style.display = "block";  
}
</script>


<?php include '../../templates/footer.tmp.php';