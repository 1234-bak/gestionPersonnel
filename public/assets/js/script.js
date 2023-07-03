const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
	const li = item.parentElement;

	item.addEventListener('click', function () {
		allSideMenu.forEach(i=> {
			i.parentElement.classList.remove('active');
		})
		li.classList.add('active');
	})
});

// TOGGLE SIDEBAR
// const menuBar = document.querySelector('#content nav .bx.bx-menu');
const menuBar = document.querySelector('#content nav .arrow');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
})

const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})

if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}

window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
})

  // Attendre que le contenu de la page soit chargé
  document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons d'accordéon
    var accordionButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
    // Ajouter un gestionnaire d'événement à chaque bouton d'accordéon
    accordionButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        // Basculer la classe "collapsed" sur le bouton pour afficher/cacher l'accordéon
        this.classList.toggle('collapsed');

        // Sélectionner l'élément cible à afficher/cacher
        var target = document.querySelector(this.getAttribute('data-bs-target'));

        // Basculer la classe "show" sur l'élément cible pour afficher/cacher le contenu de l'accordéon
        target.classList.toggle('show');
      });
    });
  });

  function toggleAccordion(element) {
	var menuItem = element.parentNode;
	var subMenu = menuItem.querySelector('.sub-menu');

	subMenu.style.display = subMenu.style.display === "block" ? "none" : "block";
  }
 