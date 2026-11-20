var celcius = prompt("Masukkan suhu dalam Celcius:", "");
if (celcius !== null && celcius !== "") {
  var c = parseFloat(celcius);
  var fahrenheit = (c * 9 / 5) + 32;
  alert("Hasil Konversi:\n" + c + " \u00B0C = " + fahrenheit + " \u00B0F");
  document.write("<h2>Hasil Konversi Suhu</h2>");
  document.write("<p>" + c + " &deg;C = " + fahrenheit + " &deg;F</p>");
} else {
  document.write("<p>Input dibatalkan.</p>");
}