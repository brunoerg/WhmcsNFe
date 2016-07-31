<p>
<b>Baixar arquivo de lote antigo</b>
</p>
<p>
    Numero do lote: <input type="text" id="lote_number" value=""/>
    <input type="button" id="downloadold" value="Download">
</p>

<p>
<b>Gerar novo(s) arquivos(s)</b>
</p>
<form method="post">
<table>
  <tr>
	<td>Data Inicial</td>
	<td><input type="text" name="txtDataInicial" id="txtDataInicial" value="<?php echo $_POST['txtDataInicial']?>" /></td>
	<td>Data Final</td>
	<td><input type="text" name="txtDataFinal" id="txtDataFinal" value="<?php echo $_POST['txtDataFinal']?>" /></td>
	<td><input type="submit" name="btnGerarNFE" id="btnGerarNFE" value="Pesquisar" /></td>
  </tr>
</table>
</form>

<script>

  $(function(){
		$("#txtDataInicial").datepicker({dateFormat: 'dd/mm/yy'});
		$("#txtDataFinal").datepicker({dateFormat: 'dd/mm/yy'});
		//$("#btnGerarNFE").button();
		//$("#btnGerarArquivo").button();

		$("#chkTodos").click(function() {
		if($(this).attr("checked") == "checked") {
			$("input[type='checkbox']").attr("checked",true);
		}
		else {
			$("input[type='checkbox']").attr("checked",false);
		}
	});

  });
  
</script>

<script>

  $(function() {
      $("#downloadold").click(function(){
          var filename = "lote-" + $("#lote_number").val() + ".txt";
          window.location = "../modules/addons/nfe2/download.php?filename=" + filename;
      });
  });
  
</script>