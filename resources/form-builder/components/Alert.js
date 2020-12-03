import Swal from 'sweetalert2/dist/sweetalert2.js'

export const Alert = (message, type = 'success', position = 'top-end') => {
    Swal.fire({
        toast: true,
        icon: type,
        title: message,
        position: position,
        showConfirmButton: false,
        timer: 2000,
        showClass: {
            popup: 'swal2-noanimation',
        },
        hideClass: {
            popup: '',
        },
    })
}
