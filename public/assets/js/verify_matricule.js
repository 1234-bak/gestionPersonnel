document.addEventListener("DOMContentLoaded", function() {
    var form = document.querySelector('#verif_form'); 

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Simulez un succès (pour tester)
        var isSuccess = true;

        if (isSuccess) {
            Swal.fire({
                title: 'Succès',
                text: 'Opération réussie !',
                icon: 'success'
            });
        } else {
            Swal.fire({
                title: 'Erreur',
                text: 'Une erreur s\'est produite.',
                icon: 'error'
            });
        }
    });
});
