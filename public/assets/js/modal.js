function showModal(elementId) {
    const modal = document.getElementById('modal');
    modal.style.display = 'block';
    
  }
//   document.addEventListener('DOMContentLoaded', function() {
//     const modal = document.getElementById('modal');
//     const modalCloseBtn = document.getElementById('modal-close');

//     modalCloseBtn.addEventListener('click', function() {
//         modal.style.display = 'none';
//     });
// });


  // Écouteur d'événement pour la soumission du formulaire
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Récupérer les nouvelles valeurs des champs modifiés
    var nouveauNom = document.querySelector('#nom').value;
    var nouveauPrenom = document.querySelector('#prenom').value;

    // Mettre à jour les valeurs dans les attributs 'data' du lien "Éditer"
    var editLink = document.querySelector('.edit-link');
    editLink.setAttribute('data-nom', nouveauNom);
    editLink.setAttribute('data-prenom', nouveauPrenom);

    // Masquer le formulaire
    document.querySelector('.modal').style.display = 'none';

    // Envoyer le formulaire pour l'enregistrement
    e.target.submit();
});
