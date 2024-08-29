function showToast(message) {
    var toastContainer = document.getElementById("toastContainer");

    var toast = document.createElement("div");
    toast.className = "toast";

    var closeBtn = document.createElement("span");
    closeBtn.className = "close";
    closeBtn.innerHTML = "&times;";
    closeBtn.onclick = function () {
        hideToast(toast);
    };

    var msg = document.createElement("div");
    msg.textContent = message;

    var progress = document.createElement("div");
    progress.className = "progress";

    var bar = document.createElement("div");
    bar.className = "bar";

    progress.appendChild(bar);
    toast.appendChild(closeBtn);
    toast.appendChild(msg);
    toast.appendChild(progress);

    toastContainer.appendChild(toast);

    // Show the toast
    setTimeout(function () {
        toast.classList.add("show");
        bar.classList.add("show");
    }, 100);

    // Hide the toast after 7 seconds
    setTimeout(function () {
        hideToast(toast);
    }, 7000);
}

function hideToast(toast) {
    toast.classList.remove("show");
    setTimeout(function () {
        toast.remove();
    }, 500); // Wait for the transition to finish before removing
}