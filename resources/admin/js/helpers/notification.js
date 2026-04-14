import Swal from "sweetalert2";

export function notification(notifyType, title, message) {
    Swal.fire({
        title: title,
        text: message,
        icon: notifyType,
        position: 'top-end',
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
    })
}
