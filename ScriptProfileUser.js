function expandImage() {
    document.getElementById("overlay").style.visibility = "visible";
    document.getElementById("overlay").style.opacity = "1";
}

function closeImage(event) {
    if (event.target.id === "overlay" || event.target.classList.contains("close-btn")) {
        document.getElementById("overlay").style.opacity = "0";
        setTimeout(() => {
            document.getElementById("overlay").style.visibility = "hidden";
        }, 300);
    }
}