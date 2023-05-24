var deleteModal = document.getElementById('deleteModal');

deleteModal.addEventListener('show.bs.modal', function (event) {
    let sourceButton = event.relatedTarget;
    let foodstop = sourceButton.getAttribute("data-bs-foodstop");
    let user = sourceButton.getAttribute("data-bs-user");

    let foodstopId = sourceButton.getAttribute("data-bs-foodstop-id");
    let userId = sourceButton.getAttribute("data-bs-user-id");
    // TODO: This is not being inserted, get hard coded text to work first, then get it dynamically
    deleteModal.querySelector(".user").innerHTML = user;
    deleteModal.querySelector(".foodstop").innerHTML = foodstop;

    deleteModal.querySelector("#removeRoleButton").addEventListener('click', function event() {
        window.location = 'admin.php?action=removeRole&userId=' + userId + '&foodStopId=' + foodstopId;
    });
});