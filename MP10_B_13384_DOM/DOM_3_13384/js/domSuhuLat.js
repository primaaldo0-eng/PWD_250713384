function konversiSuhu() {
  var celcius = parseFloat(document.getElementById("celcius").value);
  if (isNaN(celcius)) {
    document.getElementById("hasil").innerHTML = "0";
    document.getElementById("status").innerHTML = "-";
    return;
  }
  var fahrenheit = (celcius * 9 / 5) + 32;
  document.getElementById("hasil").innerHTML = fahrenheit;
  if (fahrenheit > 86) {
    document.getElementById("status").innerHTML = "Panas";
  } else {
    document.getElementById("status").innerHTML = "Sejuk";
  }
}

function resetForm() {
  document.getElementById("celcius").value = "";
  document.getElementById("hasil").innerHTML = "0";
  document.getElementById("status").innerHTML = "-";
}