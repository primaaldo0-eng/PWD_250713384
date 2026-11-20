function konversiSuhu() {
  var celcius = parseFloat(document.getElementById("celcius").value);
  if (isNaN(celcius)) {
    document.getElementById("hasil").innerHTML = "0";
    document.getElementById("keterangan").innerHTML = "Masukkan angka yang valid.";
    return;
  }
  var fahrenheit = (celcius * 9 / 5) + 32;
  document.getElementById("hasil").innerHTML = fahrenheit;
  if (fahrenheit < 86) {
    document.getElementById("keterangan").innerHTML = "Suhu Sejuk";
  } else {
    document.getElementById("keterangan").innerHTML = "Suhu Panas";
  }
}