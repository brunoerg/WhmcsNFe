<?php
include("functions.php");


if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

/**
 * Configuração do Addon
 */
function nfe2_config() {
  
  $custom_fields = listClientCustomFields();
  $configarray = array(
    'name' => 'NFE - Nota Fiscal Eletrônica',
    'version' => '1.1',
    'author' => 'Dev',
    'description' => 'Gera os arquivos TXT para enviar a Receita Federal',
    'fields' => array(
      'cnpj'                            => array("FriendlyName" => "CNPJ",                "Type" => "text", "size" => "14", "Description" => "CPF/CNPJ do Prestador"),
      'inscricao_municipal'             => array("FriendlyName" => "Incrição Municipal",  "Type" => "text", "size" => "15", "Description" => "Inscrição Municipal do Prestador"),
      'es_municipio'                    => array("FriendlyName" => "Código do Municipio", "Type" => "text", "size" => "15", "Description" => "Código do município do Prestador conforme a tabela do IBGE"),
      //'numero'                          => array("FriendlyName" => "Número",              "Type" => "text", "size" => "15", "Description" => "Número do RPS"),
      'serie'                           => array("FriendlyName" => "Série" ,              "Type" => "text", "size" => "5",  "Description" => "Série do RPS"),
      'uf'                              => array("FriendlyName" => "UF" ,                 "Type" => "text", "size" => "2",  "Description" => "Estado do município onde o imposto será tributado"),
      'lote_rps'                        => array("FriendlyName" => "Nº Lote RPS" ,        "Type" => "text", "size" => "10",  "Description" => "Numero sequencial do lote RPS (O Numero ira se auto incrementar para cada arquivo)","Default" => "0"),
      'tom_endereco_es_municipio_field_name' => array ("FriendlyName" => "Custom Field(Codigo do municipio)", "Type" => "dropdown", "Options" => $custom_fields, "Description" => "Selecione o 'custom field' de Código do município", "Default" => ""),
      'tom_cpf_cnpj_field_name' => array ("FriendlyName" => "Custom Field(CPF / CNPJ)", "Type" => "dropdown", "Options" => $custom_fields, "Description" => "Selecione o 'custom field' de CPF/CNPJ", "Default" => ""),
      'cd_natureza_operacao' => array("FriendlyName" => "Cód. da natureza da operação","Type" => "text", "size" => "10",  "Description" => "Código da natureza da operação","Default" => "2"),
      'cd_regime_especial_tributacao' => array("FriendlyName" => "Código regime espeial","Type" => "text", "size" => "10",  "Description" => "Código regime espeial","Default" => "7"),
      'sn_optante_simples_nacional' => array("FriendlyName" => "Optante pelo simples","Type" => "dropdown", "Options" => "Sim,Não", "Description" => "Optante pelo simples","Default" => "Sim"),
      'cd_status' => array("FriendlyName" => "Status do RPS","Type" => "dropdown", "Options" => "Ativa,Cancelada", "Description" => "Status do RPS","Default" => "Ativa"),
      'es_item_lista_servico' => array("FriendlyName" => "Código da atividade federal","Type" => "text", "size" => "10",  "Description" => "Código da atividade federal","Default" => "1.01"),
      'cd_tributacao_municipio' => array("FriendlyName" => "Código de atividade municipal","Type" => "text", "size" => "10",  "Description" => "Código de atividade municipal","Default" => "1.01"),
      'discriminacao' => array("FriendlyName" => "Discriminação","Type" => "text", "size" => "10",  "Description" => "Discriminação","Default" => "Revenda e Hospedagem de Websites"),
      'es_cnae' => array("FriendlyName" => "Código CNAE","Type" => "text", "size" => "10",  "Description" => "Código CNAE.(Apenas numeros)","Default" => ""),
      'vl_deducao' => array("FriendlyName" => "Valor dedução","Type" => "text", "size" => "10",  "Description" => "% dedução","Default" => "0.00"),
      'vl_pis' => array("FriendlyName" => "Valor PIS","Type" => "text", "size" => "10",  "Description" => "% PIS","Default" => "0.00"),
      'vl_cofins' => array("FriendlyName" => "Valor Cofins","Type" => "text", "size" => "10",  "Description" => "% Cofins","Default" => "0.00"),
      'vl_inss' => array("FriendlyName" => "Valor INSS","Type" => "text", "size" => "10",  "Description" => "% INSS","Default" => "0.00"),
      'vl_ir' => array("FriendlyName" => "Valor IR","Type" => "text", "size" => "10",  "Description" => "% IR","Default" => "0.00"),
      'sn_iss_retido' => array("FriendlyName" => "Retenção na fonte","Type" => "dropdown", "Options" => "Sim,Não",  "Description" => "Retenção na fonte","Default" => "Não"),
      'vl_iss' => array("FriendlyName" => "Valor ISS","Type" => "text", "size" => "10",  "Description" => "% ISS","Default" => "0.00"),
      'vl_iss_retido' => array("FriendlyName" => "Valor ISS retido","Type" => "text", "size" => "10",  "Description" => "% ISS retido","Default" => "0.00"),
      'vl_outras_retencoes' => array("FriendlyName" => "Outras Retenções","Type" => "text", "size" => "10",  "Description" => "Outras Retenções","Default" => "0.00"),
      'vl_cs' => array("FriendlyName" => "Valor da Contribuição Social","Type" => "text", "size" => "10",  "Description" => "% da Contribuição Social","Default" => "0.00"),
      'vl_aliquota' => array("FriendlyName" => "Valor Aliquota","Type" => "text", "size" => "10",  "Description" => "% Aliquota","Default" => "0.00"),
      //'vl_liquido_nfse' => array("FriendlyName" => "Valor Liquido NFSE","Type" => "text", "size" => "10",  "Description" => "Valor Liquido NFSE","Default" => "0.00"),
      'vl_desconto_incondicionado' => array("FriendlyName" => "Vl. desconto incondicionado","Type" => "text", "size" => "10",  "Description" => "% desconto incondicionado","Default" => "0.00"),
      'vl_desconto_condicionado' => array("FriendlyName" => "Vl. desconto condicionado","Type" => "text", "size" => "10",  "Description" => "% desconto condicionado","Default" => "0.00")
    ),
  );
  return $configarray;
}

/**
 * Abilita Addon
 */
function nfe2_activate()
{
    return array('status'=>'success','description'=>'Addon abilitado com sucesso');
}

/**
 * Desabilita Addon
 */
function nfe2_deactivate()
{
    return array('status'=>'success','description'=>'Addon desabilitado com sucesso');
}

/**
 * Tela de exibição do Addon
 */
function nfe2_output($aVars) {
	
	if(empty($_POST["export"]))
	{
		//Exibe Formulário
		include("form.php");
	}
	
	if(isset($_POST['txtDataInicial']) && $_POST['txtDataInicial'] != '' && isset($_POST['txtDataFinal']) && $_POST['txtDataFinal'] != '')
	{
		//Exibe Resultado (Tabela para seleção de clientes)
		include("result.php");
	}

	if(isset($_POST['dtInicial']))
	{
		//Exporta em formato txt os selecionados
		include("export.php");
	}

}

function listClientCustomFields()
{
    $q = "select fieldname from tblcustomfields where type='client'";
    $result = mysql_query($q);
    $fields = array();
    while($row = mysql_fetch_array($result))
    {
        $fields[] = $row["fieldname"];
    }

    return implode(",",$fields);
}

?>
