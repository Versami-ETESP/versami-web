function searchUsers(query) {
    if (query.length === 0) {
        document.getElementById("searchResults").innerHTML = "";
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("searchResults").innerHTML = this.responseText;
        }
    };

    xhr.open("GET", "search_users.php?q=" + query, true);
    xhr.send();
}