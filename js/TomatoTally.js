function displayTallyMap() {
    var x = document.getElementById("tallyMan");
    var y = document.getElementById("tallyMap")
    if (x.style.display === "none") {
        x.style.display = "block";
        y.style.display = "none";
    } else {
        x.style.display = "none";
        y.style.display = "block";
    }
}