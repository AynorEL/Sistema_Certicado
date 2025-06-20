</div>

</div>

<script src="js/jquery-2.2.4.min.js"></script>
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
		if (elem.value == 0) {
			document.getElementById('photo_div').style.display = "none";
			document.getElementById('icon_div').style.display = "none";
		}
		if (elem.value == 1) {
			document.getElementById('photo_div').style.display = "block";
			document.getElementById('photo_div_existing').style.display = "block";
			document.getElementById('icon_div').style.display = "none";
		}
		if (elem.value == 2) {
			document.getElementById('photo_div').style.display = "none";
			document.getElementById('photo_div_existing').style.display = "none";
			document.getElementById('icon_div').style.display = "block";
		}
	}

	function showContentInputArea(elem) {
		if (elem.value == 'Diseño de página a ancho completo') {
			document.getElementById('showPageContent').style.display = "block";
		} else {
			document.getElementById('showPageContent').style.display = "none";
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
		items[i] = document.getElementById("tabField" + i);
	}

	items[1].style.display = 'block';
	items[2].style.display = 'block';
	items[3].style.display = 'block';
	items[4].style.display = 'none';

	items[5].style.display = 'block';
	items[6].style.display = 'block';
	items[7].style.display = 'block';
	items[8].style.display = 'none';

	items[9].style.display = 'block';
	items[10].style.display = 'block';
	items[11].style.display = 'block';
	items[12].style.display = 'none';

	items[13].style.display = 'block';
	items[14].style.display = 'block';
	items[15].style.display = 'block';
	items[16].style.display = 'none';

	items[17].style.display = 'block';
	items[18].style.display = 'block';
	items[19].style.display = 'block';
	items[20].style.display = 'none';

	items[21].style.display = 'block';
	items[22].style.display = 'block';
	items[23].style.display = 'block';
	items[24].style.display = 'none';

	function funcTab1(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[1].style.display = 'block';
			items[2].style.display = 'block';
			items[3].style.display = 'block';
			items[4].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[1].style.display = 'none';
			items[2].style.display = 'none';
			items[3].style.display = 'none';
			items[4].style.display = 'block';
		}
	};

	function funcTab2(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[5].style.display = 'block';
			items[6].style.display = 'block';
			items[7].style.display = 'block';
			items[8].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[5].style.display = 'none';
			items[6].style.display = 'none';
			items[7].style.display = 'none';
			items[8].style.display = 'block';
		}
	};

	function funcTab3(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[9].style.display = 'block';
			items[10].style.display = 'block';
			items[11].style.display = 'block';
			items[12].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[9].style.display = 'none';
			items[10].style.display = 'none';
			items[11].style.display = 'none';
			items[12].style.display = 'block';
		}
	};

	function funcTab4(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[13].style.display = 'block';
			items[14].style.display = 'block';
			items[15].style.display = 'block';
			items[16].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[13].style.display = 'none';
			items[14].style.display = 'none';
			items[15].style.display = 'none';
			items[16].style.display = 'block';
		}
	};

	function funcTab5(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[17].style.display = 'block';
			items[18].style.display = 'block';
			items[19].style.display = 'block';
			items[20].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[17].style.display = 'none';
			items[18].style.display = 'none';
			items[19].style.display = 'none';
			items[20].style.display = 'block';
		}
	};

	function funcTab6(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			items[21].style.display = 'block';
			items[22].style.display = 'block';
			items[23].style.display = 'block';
			items[24].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			items[21].style.display = 'none';
			items[22].style.display = 'none';
			items[23].style.display = 'none';
			items[24].style.display = 'block';
		}
	};
</script>

</body>

</html>