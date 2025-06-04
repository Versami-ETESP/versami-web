function changeTab(index) {
    let tabs = document.querySelectorAll(".tab");
    let contents = document.querySelectorAll(".contentPosts");
    
    tabs.forEach(tab => tab.classList.remove("active"));
    contents.forEach(content => content.classList.remove("active"));
    
    tabs[index].classList.add("active");
    contents[index].classList.add("active");
}