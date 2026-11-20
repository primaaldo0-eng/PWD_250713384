var suhu = 24;
var modeSaatIni = "Normal";
var MIN_SUHU = 16;
var MAX_SUHU = 30;

function updateDisplay() {
  document.getElementById("suhu").innerHTML = suhu;
  var status = "";
  if (suhu <= 20) {
    status = "Dingin";
  } else if (suhu >= 27) {
    status = "Hangat";
  } else {
    status = "Normal";
  }
  document.getElementById("status").innerHTML = status;
  document.getElementById("mode").innerHTML = modeSaatIni;
}

function naikkanSuhu() {
  if (suhu < MAX_SUHU) {
    suhu++;
  }
  updateDisplay();
}

function turunkanSuhu() {
  if (suhu > MIN_SUHU) {
    suhu--;
  }
  updateDisplay();
}

function modeHujan() {
  modeSaatIni = "Hujan";
  updateDisplay();
}

function modeNormal() {
  modeSaatIni = "Normal";
  updateDisplay();
}