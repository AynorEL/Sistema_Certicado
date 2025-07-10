</div>

</div>

<script>
// Detectar modo del navegador y actualizar favicon dinámicamente
function updateFavicon() {
    const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const favicon = '<?php echo $favicon; ?>';
    
    if (favicon) {
        const faviconLink = document.querySelector('link[rel="icon"]');
        const shortcutLink = document.querySelector('link[rel="shortcut icon"]');
        const faviconPath = '../assets/uploads/' + favicon;
        
        if (faviconLink) {
            faviconLink.href = faviconPath;
        }
        if (shortcutLink) {
            shortcutLink.href = faviconPath;
        }
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', updateFavicon);

// Escuchar cambios en el modo del navegador
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateFavicon);
}
</script>

<!-- Bootstrap 3.4.1 -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="js/jquery.inputmask.date.extensions.js"></script>
<script src="js/jquery.inputmask.extensions.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/icheck.min.js"></script>
<script src="js/fastclick.js"></script>
<script src="js/jquery.sparkline.min.js"></script>
<script src="js/jquery.slimscroll.min.js"></script>
<script src="js/jquery.fancybox.pack.js"></script>
<script src="js/app.min.js"></script>
<script src="js/jscolor.js"></script>
<script src="js/on-off-switch.js"></script>
<script src="js/on-off-switch-onload.js"></script>
<script src="js/clipboard.min.js"></script>
<script src="js/demo.js"></script>
<script src="js/summernote.js"></script>

<script>
	$(document).ready(function() {
		$('#editor1').summernote({
			height: 300
		});
		$('#editor2').summernote({
			height: 300
		});
		$('#editor3').summernote({
			height: 300
		});
		$('#editor4').summernote({
			height: 300
		});
		$('#editor5').summernote({
			height: 300
		});
	});
	$(".top-cat").on('change', function() {
		var id = $(this).val();
		var dataString = 'id=' + id;
		$.ajax({
			type: "POST",
			url: "get-mid-category.php",
			data: dataString,
			cache: false,
			success: function(html) {
				$(".mid-cat").html(html);
			}
		});
	});
	$(".mid-cat").on('change', function() {
		var id = $(this).val();
		var dataString = 'id=' + id;
		$.ajax({
			type: "POST",
			url: "get-end-category.php",
			data: dataString,
			cache: false,
			success: function(html) {
				$(".end-cat").html(html);
			}
		});
	});
</script>

<script>
	$(function() {

		// Inicializar los elementos Select2
		$(".select2").select2();

		// Mascarilla de fecha dd/mm/yyyy
		$("#datemask").inputmask("dd-mm-yyyy", {
			"placeholder": "dd-mm-yyyy"
		});
		// Mascarilla de fecha mm/dd/yyyy
		$("#datemask2").inputmask("mm-dd-yyyy", {
			"placeholder": "mm-dd-yyyy"
		});
		// Máscara de dinero en euros
		$("[data-mask]").inputmask();

		// Selector de fecha
		$('#datepicker').datepicker({
			autoclose: true,
			format: 'dd-mm-yyyy',
			todayBtn: 'linked',
		});

		$('#datepicker1').datepicker({
			autoclose: true,
			format: 'dd-mm-yyyy',
			todayBtn: 'linked',
		});

		// iCheck para entradas de checkbox y radio
		$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
			checkboxClass: 'icheckbox_minimal-blue',
			radioClass: 'iradio_minimal-blue'
		});
		// Esquema de color rojo para iCheck
		$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
			checkboxClass: 'icheckbox_minimal-red',
			radioClass: 'iradio_minimal-red'
		});
		// Esquema de color plano rojo para iCheck
		$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
			checkboxClass: 'icheckbox_flat-green',
			radioClass: 'iradio_flat-green'
		});

		$("#example1").DataTable();
		$('#example2').DataTable({
			"paging": true,
			"lengthChange": false,
			"searching": false,
			"ordering": true,
			"info": true,
			"autoWidth": false
		});

		$('#confirm-delete').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		});

		$('#confirm-approve').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		});

	});

	function confirmDelete() {
		return confirm("¿Está seguro de que desea eliminar estos datos?");
	}

	function confirmActive() {
		return confirm("¿Está seguro de que desea activar esto?");
	}

	function confirmInactive() {
		return confirm("¿Está seguro de que desea desactivar esto?");
	}
</script>

<script type="text/javascript">
	function showDiv(elem) {
		var photoDiv = document.getElementById('photo_div');
		var photoDivExisting = document.getElementById('photo_div_existing');
		var iconDiv = document.getElementById('icon_div');
		
		if (elem.value == 0) {
			if (photoDiv) photoDiv.style.display = "none";
			if (iconDiv) iconDiv.style.display = "none";
		}
		if (elem.value == 1) {
			if (photoDiv) photoDiv.style.display = "block";
			if (photoDivExisting) photoDivExisting.style.display = "block";
			if (iconDiv) iconDiv.style.display = "none";
		}
		if (elem.value == 2) {
			if (photoDiv) photoDiv.style.display = "none";
			if (photoDivExisting) photoDivExisting.style.display = "none";
			if (iconDiv) iconDiv.style.display = "block";
		}
	}

	function showContentInputArea(elem) {
		var showPageContent = document.getElementById('showPageContent');
		if (elem.value == 'Diseño de página a ancho completo') {
			if (showPageContent) showPageContent.style.display = "block";
		} else {
			if (showPageContent) showPageContent.style.display = "none";
		}
	}
</script>

<script type="text/javascript">
	$(document).ready(function() {

		$("#btnAddNew").click(function() {

			var rowNumber = $("#ProductTable tbody tr").length;

			var trNew = "";

			var addLink = "<div class=\"upload-btn" + rowNumber + "\"><input type=\"file\" name=\"photo[]\"  style=\"margin-bottom:5px;\"></div>";

			var deleteRow = "<a href=\"javascript:void()\" class=\"Delete btn btn-danger btn-xs\">X</a>";

			trNew = trNew + "<tr> ";

			trNew += "<td>" + addLink + "</td>";
			trNew += "<td style=\"width:28px;\">" + deleteRow + "</td>";

			trNew = trNew + " </tr>";

			$("#ProductTable tbody").append(trNew);

		});

		$('#ProductTable').delegate('a.Delete', 'click', function() {
			$(this).parent().parent().fadeOut('slow').remove();
			return false;
		});

	});

	var items = [];
	for (i = 1; i <= 24; i++) {
		var element = document.getElementById("tabField" + i);
		if (element) {
			items[i] = element;
		}
	}

	// Solo aplicar estilos si los elementos existen
	if (items[1]) items[1].style.display = 'block';
	if (items[2]) items[2].style.display = 'block';
	if (items[3]) items[3].style.display = 'block';
	if (items[4]) items[4].style.display = 'none';

	if (items[5]) items[5].style.display = 'block';
	if (items[6]) items[6].style.display = 'block';
	if (items[7]) items[7].style.display = 'block';
	if (items[8]) items[8].style.display = 'none';

	if (items[9]) items[9].style.display = 'block';
	if (items[10]) items[10].style.display = 'block';
	if (items[11]) items[11].style.display = 'block';
	if (items[12]) items[12].style.display = 'none';

	if (items[13]) items[13].style.display = 'block';
	if (items[14]) items[14].style.display = 'block';
	if (items[15]) items[15].style.display = 'block';
	if (items[16]) items[16].style.display = 'none';

	if (items[17]) items[17].style.display = 'block';
	if (items[18]) items[18].style.display = 'block';
	if (items[19]) items[19].style.display = 'block';
	if (items[20]) items[20].style.display = 'none';

	if (items[21]) items[21].style.display = 'block';
	if (items[22]) items[22].style.display = 'block';
	if (items[23]) items[23].style.display = 'block';
	if (items[24]) items[24].style.display = 'none';

	function funcTab1(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[1]) items[1].style.display = 'block';
			if (items[2]) items[2].style.display = 'block';
			if (items[3]) items[3].style.display = 'block';
			if (items[4]) items[4].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[1]) items[1].style.display = 'none';
			if (items[2]) items[2].style.display = 'none';
			if (items[3]) items[3].style.display = 'none';
			if (items[4]) items[4].style.display = 'block';
		}
	};

	function funcTab2(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[5]) items[5].style.display = 'block';
			if (items[6]) items[6].style.display = 'block';
			if (items[7]) items[7].style.display = 'block';
			if (items[8]) items[8].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[5]) items[5].style.display = 'none';
			if (items[6]) items[6].style.display = 'none';
			if (items[7]) items[7].style.display = 'none';
			if (items[8]) items[8].style.display = 'block';
		}
	};

	function funcTab3(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[9]) items[9].style.display = 'block';
			if (items[10]) items[10].style.display = 'block';
			if (items[11]) items[11].style.display = 'block';
			if (items[12]) items[12].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[9]) items[9].style.display = 'none';
			if (items[10]) items[10].style.display = 'none';
			if (items[11]) items[11].style.display = 'none';
			if (items[12]) items[12].style.display = 'block';
		}
	};

	function funcTab4(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[13]) items[13].style.display = 'block';
			if (items[14]) items[14].style.display = 'block';
			if (items[15]) items[15].style.display = 'block';
			if (items[16]) items[16].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[13]) items[13].style.display = 'none';
			if (items[14]) items[14].style.display = 'none';
			if (items[15]) items[15].style.display = 'none';
			if (items[16]) items[16].style.display = 'block';
		}
	};

	function funcTab5(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[17]) items[17].style.display = 'block';
			if (items[18]) items[18].style.display = 'block';
			if (items[19]) items[19].style.display = 'block';
			if (items[20]) items[20].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[17]) items[17].style.display = 'none';
			if (items[18]) items[18].style.display = 'none';
			if (items[19]) items[19].style.display = 'none';
			if (items[20]) items[20].style.display = 'block';
		}
	};

	function funcTab6(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[21]) items[21].style.display = 'block';
			if (items[22]) items[22].style.display = 'block';
			if (items[23]) items[23].style.display = 'block';
			if (items[24]) items[24].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[21]) items[21].style.display = 'none';
			if (items[22]) items[22].style.display = 'none';
			if (items[23]) items[23].style.display = 'none';
			if (items[24]) items[24].style.display = 'block';
		}
	};
</script>

</body>

</html>