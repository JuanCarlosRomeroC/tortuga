<?php
class lhojadetrabajo extends Controller {

	function lhojadetrabajo() {
		parent::Controller();
	}
	function index() {
		$this->load->library('mtable');
		//$this->load->view('table_def.inc');
		
		$obj = new mtable("Listado de Hoja de Trabajo p/toma de Inventario F�sico",8);
		$obj->AddPage();
		$obj->AddCol('codigo' ,20, 'C�digo', 'L',6);
		$obj->AddCol('grupo'  ,10, 'Grupo', 'C',6);
		$obj->AddCol('descrip',125,'Descripci�n', 'L',6);
		$obj->AddCol('exist'  ,15, 'Existencia', 'L',6);
		
		$obj->Table("SELECT codigo,grupo,descrip, '    ' exist FROM sinv order by codigo" );
		$obj->Output();
	}
}
 ?>
