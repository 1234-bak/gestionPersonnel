$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#example')) {
        // Si oui, détruire DataTable
        $('#example').DataTable().destroy();
    }
    // $('.select2').select2({
    //     theme: 'classic', 
    //     minimumResultsForSearch: 1 
    // });
    $('.dropdown-toggle').dropdown();
    

    // Initialiser DataTable
    var table = $('#example').DataTable({
        language: {
            url: "/assets/lang/French.json"
        },
    });
    

    
    
});
