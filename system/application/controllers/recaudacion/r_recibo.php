<?php
class r_recibo extends Controller {
	var $titp='Recibos';
	var $tits='Recibo';
	var $url ='recaudacion/r_recibo/';
	function r_recibo(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(409,1);
		$user   = $this->session->userdata('usuario');
		$usere  = $this->db->escape($user);
		$this->cajan   = $this->datasis->dameval("SELECT r_caja.nombre FROM r_caja JOIN  usuario ON r_caja.id=usuario.caja WHERE us_codigo =$usere");
	}
	function index(){
		redirect($this->url."filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'r_recibo');
		
		$user          = $this->session->userdata('usuario');
		$usere         = $this->db->escape($user);
		$r_caja        = $this->datasis->damerow("SELECT r_caja.id,punto_codbanc FROM r_caja JOIN  usuario ON r_caja.id=usuario.caja WHERE us_codigo =$usere");
		
		if(count($r_caja)>0){
			$caja          = $r_caja['id'];
			$punto_codbanc = $r_caja['punto_codbanc'];
		}else{
			$caja=0;
		}
		if($caja>0){
				$filter->db->where('caja',$caja);
		}

		$filter->id = new inputField('Ref.','id');
		$filter->id->rule      ='max_length[11]';
		$filter->id->size      =13;
		$filter->id->maxlength =11;
		$filter->id->clause    ='where';
		$filter->id->operator  ='=';

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;
		$filter->fecha->clause    ='where';
		$filter->fecha->operator  ='=';
		
		$filter->numero = new inputField('Numero.','numero');
		$filter->numero->rule      ='max_length[11]';
		$filter->numero->size      =13;
		$filter->numero->maxlength =11;
		$filter->numero->clause    ='where';
		$filter->numero->operator  ='=';

		$filter->rifci = new inputField('rifci','rifci');
		$filter->rifci->rule      ='max_length[12]';
		$filter->rifci->size      =14;
		$filter->rifci->maxlength =12;

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[100]';
		$filter->nombre->size      =40;
		$filter->nombre->maxlength =100;
		
		$filter->monto = new inputField('Monto.','monto');
		$filter->monto->rule      ='max_length[11]';
		$filter->monto->size      =13;
		$filter->monto->maxlength =11;
		
		$filter->caja = new dropdownField("Caja","cajas");
		$filter->caja->option("","");
		$filter->caja->options("SELECT id,nombre FROM r_caja ");
		$filter->caja->db_name   ='r_recibo.caja';


		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Ref.'         ,"$uri",'id','align="left"');
		$grid->column_orderby('Numero'     ,"numero",'numero','align="left"');
		$grid->column_orderby('Fecha',"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha','align="center"');
		$grid->column_orderby('Rif/CI',"rifci",'rifci','align="left"');
		$grid->column_orderby('Nombre',"nombre",'nombre','align="left"');
		$grid->column_orderby('Monto',"<numbre_format><#monto#></numbre_format>",'monto','align="left"');
		$grid->column_orderby('Caja'  ,"caja",'caja','align="left"');
		
		$action = "javascript:window.location='" .site_url('recaudacion/r_abonos/filteredgrid'). "'";
		$grid->button("ir_cobranza","Ir a Cobranza",$action,"TL");

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = $this->titp.($this->cajan?" CAJA $this->cajan":"");;
		$this->load->view('view_ventanas', $data);

	}
	function dataedit($id_contribu=false){
		$this->rapyd->load('datadetails','dataobject');
		
		$modbus=array(
			'tabla'   =>'r_contribu',
			'columnas'=>array(
				'id'    =>'Ref.',
				'rifci' =>'Rif/CI',
				'nombre'=>'Nombre'
				),
			'filtro'  =>array(
				'id'    =>'Ref.',
				'rifci' =>'Rif/CI',
				'nombre'=>'Nombre'
			),
			'retornar'=>array(
				'id'    =>'id_contribu',
				'nombre'=>'nombre',
				'rifci' =>'rifci'
			),
			'titulo'  =>'Buscar Contribuyente',
			//'script'  =>array('cargadeuda()'),
		);

		$button  = $this->datasis->modbus($modbus);
		
		$modbus=array(
			'tabla'   =>'r_v_conc',
			'columnas'=>array(
				'id'          =>'Ref',          
				'ano'         =>'A&ntilde;o',        
				'acronimo'    =>'Acronimo',     
				'denomi'      =>'Denominacion', 
				'denomiconc'  =>'Denomi Padre', 
				'partida'     =>'Partida',     
			),
			'filtro'  =>array(
				'denomi'      =>'Denominacion', 
				'denomiconc'  =>'Denomi Padre', 
				'partida'     =>'Partida',     
			),
			'retornar'=>array(
				'id'          =>'id_concit_<#i#>',
				'ano'         =>'ano_<#i#>',
				'acronimo'    =>'acronimo_<#i#>',
				'denomi'      =>'denomi_<#i#>',
				'requiere'    =>'requiere_<#i#>'
			),
			'titulo'  =>'Buscar Concepto',
			'script'  =>array('post_conc(<#i#>)','traemonto(<#i#>)'),
			'p_uri'=>array(
				4=>'<#i#>'
			)
		);

		$buttonconc  = $this->datasis->p_modbus($modbus,'<#i#>' );
		
		$mVEHICULO=array(
				'tabla'   =>'r_v_vehiculo',
				'columnas' =>array(
					'id'       =>'Ref.',
					'placa'    =>'Placa',
					'marca'    =>'Marca',
					'modelo'   =>'Modelo',
					'color'    =>'Color',
					'tipo'     =>'Tipo' ,
					'clase'    =>'Clase'
					),
				'filtro'  =>array(
					'id'       =>'Ref.',
					'placa'    =>'Placa',
					'marca'    =>'Marca',
					'modelo'   =>'Modelo',
					'color'    =>'Color',
					'tipo'     =>'Tipo' ,
					'clase'    =>'Clase'
					),
				'retornar'=>array(
					'id'       =>'id_vehiculo_<#i#>',
					'placa'    =>'v_placa_<#i#>'
					),
				'p_uri'  =>array(
					4=>'<#id_contribu#>',
					5=>'<#i#>'
					),
				'where'  =>'id_contribu = <#id_contribu#>',
				'script' =>array('traemonto(<#i#>)'),
				'titulo' =>'Buscar Vehiculo');
		
		$bVEHICULO=$this->datasis->p_modbus($mVEHICULO,'<#id_contribu#>/<#i#>');
		$bVEHICULO='<img id="modbusv_<#i#>" src="'.base_url().'assets/default/images/system-search.png"  alt="Busqueda de Vehiculos" title="Busqueda de Vehiculos" border="0" onclick="modbusdepenv(<#i#>)"/>';
		
		$mINMUEBLE=array(
				'tabla'   =>'r_v_inmueble',
				'columnas' =>array(
					'id'       =>'Ref.',
					'catastro' =>'Catastro',
					'direccion'=>'Direccion',
					'mt2'      =>'Mts2',
					'techo'    =>'Techo',
					),
				'filtro'  =>array(
					'id'       =>'Ref.',
					'catastro' =>'Catastro',
					'direccion'=>'Direccion',
					'mt2'      =>'Mts2',
					'techo'    =>'Techo',
					),
				'retornar'=>array(
					'id'       =>'id_inmueble_<#i#>',
					'catastro' =>'i_catastro_<#i#>',
					'direccion'=>'observa_<#i#>',
					),
				'p_uri'  =>array(
					4=>'<#id_contribu#>',
					5=>'<#i#>'
					),
				'where'  =>'id_contribu = <#id_contribu#>',
				'script' =>array('traemonto(<#i#>)'),
				'titulo' =>'Buscar Inmueble');
		
		$bINMUEBLE=$this->datasis->p_modbus($mINMUEBLE,'<#id_contribu#>/<#i#>');
		$bINMUEBLE='<img id="modbusi_<#i#>" src="/tortuga/assets/default/images/system-search.png"  alt="Busqueda de Vehiculos" title="Busqueda de Vehiculos" border="0" onclick="modbusdepeni(<#i#>)"/>';
		
		$mPUBLICIDAD=array(
				'tabla'   =>'r_v_publicidad',
				'columnas' =>array(
					'id'       =>'Ref.',
					'direccion'=>'Direccion',
					'ancho'    =>'Ancho',
					'alto'     =>'Alto', 
					'descrip'  =>'Descripcion',
					),
				'filtro'  =>array(
					'id'       =>'Ref.',
					'direccion'=>'Direccion',
					'ancho'    =>'Ancho',
					'alto'     =>'Alto', 
					'descrip'  =>'Descripcion',
					),
				'retornar'=>array(
					'id'       =>'id_publicidad_<#i#>',
					'CONCAT(ancho,"X",alto," mts2")'=>'observa_<#i#>',
					),
				'p_uri'  =>array(
					4=>'<#id_contribu#>',
					5=>'<#i#>'
					),
				'where'  =>'id_contribu = <#id_contribu#>',
				'script' =>array('traemonto(<#i#>)'),
				'titulo' =>'Buscar Vehiculo');
		
		$bPUBLICIDAD=$this->datasis->p_modbus($mPUBLICIDAD,'<#id_contribu#>/<#i#>');
		$bPUBLICIDAD='<img id="modbusp_<#i#>" src="/tortuga/assets/default/images/system-search.png"  alt="Busqueda de Publicidades" title="Busqueda de Publicidad" border="0" onclick="modbusdepenp(<#i#>)"/>';
		
		$mINMUEBLET=array(
				'tabla'   =>'r_v_inmueble',
				'columnas' =>array(
					'id'       =>'Ref.',
					'catastro' =>'Catastro',
					'direccion'=>'Direccion',
					'mt2'      =>'Mts2',
					'tipoi'    =>'Tipo',
					),
				'filtro'  =>array(
					'id'       =>'Ref.',
					'catastro' =>'Catastro',
					'direccion'=>'Direccion',
					'mt2'      =>'Mts2',
					'tipoi'    =>'Tipo',
					),
				'retornar'=>array(
					'id'       =>'id_inmueble'
					),
				'p_uri'  =>array(
					4=>'<#id_contribu#>'
					),
				'where'  =>'id_contribu = <#id_contribu#>',
				'script' =>array('post_inmueblet()'),
				'titulo' =>'Buscar Inmueble');
		
		$bINMUEBLET=$this->datasis->modbus($mINMUEBLET,'r_v_inmueblet',800,600,'r_v_inmueblet');
		$bINMUEBLET='<img id="modbusit" src="/tortuga/assets/default/images/system-search.png"  alt="Busqueda de Inmuebles" title="Busqueda de Inmuebles" border="0" onclick="modbusdepenit()"/>';
		
		$mVEHICULOT=array(
				'tabla'   =>'r_v_vehiculo',
				'columnas' =>array(
					'id'       =>'Ref.',
					'placa'    =>'Placa',
					'marca'    =>'Marca',
					'modelo'   =>'Modelo',
					'color'    =>'Color',
					'tipo'     =>'Tipo' ,
					'clase'    =>'Clase'
					),
				'filtro'  =>array(
					'id'       =>'Ref.',
					'placa'    =>'Placa',
					'marca'    =>'Marca',
					'modelo'   =>'Modelo',
					'color'    =>'Color',
					'tipo'     =>'Tipo' ,
					'clase'    =>'Clase'
					),
				'retornar'=>array(
					'id'       =>'id_vehiculo'
					),
				'p_uri'  =>array(
					4=>'<#id_contribu#>'
					),
				'where'  =>'id_contribu = <#id_contribu#>',
				'script' =>array('post_vehiculot()'),
				'titulo' =>'Buscar Vehiculos');
		
		$bVEHICULOT=$this->datasis->modbus($mVEHICULOT,'r_v_vehiculot',800,600,'r_v_vehiculot');
		$bVEHICULOT='<img id="modbusvt" src="/tortuga/assets/default/images/system-search.png"  alt="Busqueda de Vehiculos" title="Busqueda de Vehiculos" border="0" onclick="modbusdepenvt()"/>';
		
		
		$contribu=array();
		$contribu['id']     ='';
		$contribu['nombre'] ='';
		$contribu['rifci']  ='';
		if($id_contribu>0){
				$contribu=$this->datasis->damerow("SELECT id,rifci,nombre FROM r_contribu WHERE id=$id_contribu");
		}
		
		$do = new DataObject("r_recibo");
		$do->rel_one_to_many('r_reciboit'   , 'r_reciboit'   , array('id' =>'id_recibo'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url."filteredgrid");

		$edit->pre_process('insert','_valida');
		$edit->pre_process('update','_valida');
		$edit->pre_process('delete','_pre_delete');
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->id = new inputField('Ref','id');
		$edit->id->rule='max_length[11]';
		$edit->id->size =2;
		$edit->id->maxlength =11;
		$edit->id->mode='autohide';
		$edit->id->when=array('show','modify');
		
		$edit->id_contribu = new inputField('Contribuyente','id_contribu');
		$edit->id_contribu->rule='required';
		$edit->id_contribu->size =5;
		$edit->id_contribu->maxlength =11;
		$edit->id_contribu->append($button);
		$edit->id_contribu->readonly=true;
		$edit->id_contribu->value   =$contribu['id'] ;

		$crea = '<a href="javascript:creacontribu();" title="Agregar Contribuyente">'.image('add.png','#',array("border"=>0)).'</a>';
		$edit->rifci = new inputField('rifci','rifci');
		$edit->rifci->rule='max_length[12]';
		$edit->rifci->size =14;
		$edit->rifci->maxlength =12;
		$edit->rifci->append($crea);
		$edit->rifci->autocomplete=false;
		$edit->rifci->value   =$contribu['rifci'] ;

		$edit->nombre = new inputField('nombre','nombre');
		$edit->nombre->rule='max_length[100]';
		$edit->nombre->size =40;
		$edit->nombre->maxlength =100;
		$edit->nombre->value   =$contribu['nombre'] ;
		
		$edit->fecha = new dateOnlyField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;
		$edit->fecha->insertValue=date('Y-m-d');
		
		$user   = $this->session->userdata('usuario');
		$usere  = $this->db->escape($user);
		$numero = $this->datasis->dameval("SELECT r_contador.proxnumero FROM r_contador JOIN r_caja ON r_contador.id=r_caja.id_contador JOIN  usuario ON r_caja.id=usuario.caja WHERE us_codigo =$usere");
		
		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='trim|numeric';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;
		$edit->numero->append("Proximo:".$numero);
		//$edit->numero->type="inputhidden";
		//$edit->numero->when=array('show','modify');
		$edit->numero->css_class='inputnum';
		//$edit->numero->readonly = true;
		
		$edit->monto = new inputField('Monto','monto');
		$edit->monto->readonly=true;
		$edit->monto->size =5;
		$edit->monto->css_class ="inputnum";
		
		$edit->solvencia = new inputField('Solvencia','solvencia');
		$edit->solvencia->mode='autohide';
		$edit->solvencia->size =5;
		$edit->solvencia->when=array('show','modify');
		
		$edit->id_inmueble = new hiddenField('id_inmueble','id_inmueble');
		$edit->id_inmueble->rule='max_length[11]';
		$edit->id_inmueble->size =6;
		$edit->id_inmueble->maxlength =11;
		$edit->id_inmueble->db_name=' ';
		$edit->id_inmueble->append($bINMUEBLET);
		
		$edit->id_vehiculo = new hiddenField('id_vehiculo','id_vehiculo');
		$edit->id_vehiculo->rule='max_length[11]';
		$edit->id_vehiculo->size =6;
		$edit->id_vehiculo->maxlength =11;
		$edit->id_vehiculo->db_name=' ';
		$edit->id_vehiculo->append($bVEHICULOT);
		
		/*
		 * DETALLE
		 * 
		 * */
		 
		 
		$edit->itid_cxcit = new inputField('id_cxcit','id_cxcit_<#i#>');
		$edit->itid_cxcit->rule='max_length[11]';
		$edit->itid_cxcit->size =3;
		$edit->itid_cxcit->maxlength =11;
		$edit->itid_cxcit->db_name='id_cxcit';
		$edit->itid_cxcit->rel_id ='r_reciboit';
		$edit->itid_cxcit->type='hidden';
		
		$edit->itid =  new inputField("(<#o#>) Referencia", 'id_<#i#>');
		$edit->itid->db_name   = 'id';
		$edit->itid->rel_id    ='r_reciboit';
		//$edit->itid->when     =array('show');
		$edit->itid->readonly=true;
		$edit->itid->size      =1;
		$edit->itid->type      ='hidden';
		
		$edit->itid_conc =  new inputField("(<#o#>) Id Concepto", 'id_conc_<#i#>');
		$edit->itid_conc->db_name   = 'id_conc';
		$edit->itid_conc->rel_id    = 'r_reciboit';
		$edit->itid_conc->readonly  = true;
		$edit->itid_conc->size      = 1;
		$edit->itid_conc->type      = 'hidden';
		 
		$edit->itid_concit = new inputField('id_concit','id_concit_<#i#>');
		$edit->itid_concit->rule='max_length[11]';
		$edit->itid_concit->size =3;
		$edit->itid_concit->maxlength =11;
		$edit->itid_concit->db_name='id_concit';
		$edit->itid_concit->rel_id ='r_reciboit';
		$edit->itid_concit->append($buttonconc);
		
		$edit->itrequiere = new hiddenField('requiere','requiere_<#i#>');
		$edit->itrequiere->rule='max_length[11]';
		$edit->itrequiere->size =3;
		$edit->itrequiere->maxlength =11;
		$edit->itrequiere->db_name='requiere';
		$edit->itrequiere->rel_id ='r_reciboit';
		
		$edit->itmodo = new hiddenField('modo','modo_<#i#>');
		$edit->itmodo->rule='max_length[11]';
		$edit->itmodo->size =3;
		$edit->itmodo->maxlength =11;
		$edit->itmodo->db_name='modo';
		$edit->itmodo->rel_id ='r_reciboit';
		
		$edit->itdenomi = new inputField('denomi','denomi_<#i#>');
		$edit->itdenomi->rule='max_length[80]';
		$edit->itdenomi->size =20;
		$edit->itdenomi->maxlength =80;
		$edit->itdenomi->db_name='denomi';
		$edit->itdenomi->rel_id ='r_reciboit';
		
		$edit->itfrecuencia = new dropDownField('(<#o#>) frecuencia','frecuencia_<#i#>');
		$edit->itfrecuencia->option('0','');
		$edit->itfrecuencia->option('1','A&ntilde;o'  );
		$edit->itfrecuencia->option('2','Semestre'    );
		$edit->itfrecuencia->option('3','Trimestre'   );
		$edit->itfrecuencia->option('4','Mes'         );
		$edit->itfrecuencia->style="width:50px;";
		$edit->itfrecuencia->db_name='frecuencia';
        $edit->itfrecuencia->rel_id ='r_reciboit';
        
        $edit->itfreval = new dropDownField('(<#o#>) Valor Frecuencia','freval_<#i#>');
		$edit->itfreval->option('0','');
		for($i=1;$i<=12;$i++)
		$edit->itfreval->option($i,$i);
		$edit->itfreval->style="width:50px;";
		$edit->itfreval->db_name='freval';
        $edit->itfreval->rel_id ='r_reciboit';
		
		$edit->itano = new inputField('ano','ano_<#i#>');
		$edit->itano->rule='max_length[11]';
		$edit->itano->size =5;
		$edit->itano->maxlength =11;
		$edit->itano->db_name='ano';
		$edit->itano->rel_id ='r_reciboit';
		
		$edit->itobserva = new textAreaField('observa','observa_<#i#>');
		$edit->itobserva->rule='max_length[255]';
		$edit->itobserva->cols =20;
		$edit->itobserva->rows =1;
		$edit->itobserva->maxlength =255;
		$edit->itobserva->db_name='observa';
		$edit->itobserva->rel_id ='r_reciboit';
		
		$edit->itbase = new inputField('Base','base_<#i#>');
		$edit->itbase->rule='max_length[19]|numeric';
		$edit->itbase->css_class='inputnum';
		$edit->itbase->size =5;
		$edit->itbase->maxlength =19;
		$edit->itbase->db_name='base';
		$edit->itbase->rel_id ='r_reciboit';
		$edit->itbase->onchange = "cal_base(<#i#>);";

		$edit->itmonto = new inputField('monto','monto_<#i#>');
		$edit->itmonto->rule='max_length[19]|numeric';
		$edit->itmonto->css_class='inputnum';
		$edit->itmonto->size =5;
		$edit->itmonto->maxlength =19;
		$edit->itmonto->db_name='monto';
		$edit->itmonto->rel_id ='r_reciboit';
		$edit->itmonto->onchange = "cal_total();";
		$edit->itmonto->readonly=true;

		$crea = '<a id="creav_<#i#>" href="javascript:creavehiculoid(<#i#>);" title="Agregar/modificar Vehiculo">'.image('add.png','#',array("border"=>0)).'</a>';
		$edit->itid_vehiculo = new inputField('id_vehiculo','id_vehiculo_<#i#>');
		$edit->itid_vehiculo->rule='max_length[11]';
		$edit->itid_vehiculo->size =3;
		$edit->itid_vehiculo->maxlength =11;
		$edit->itid_vehiculo->db_name='id_vehiculo';
		$edit->itid_vehiculo->rel_id ='r_reciboit';
		$edit->itid_vehiculo->append($bVEHICULO);
		$edit->itid_vehiculo->append($crea);
		
		$edit->itv_placa = new inputField('v_placa','v_placa_<#i#>');
		$edit->itv_placa->rule='max_length[12]';
		$edit->itv_placa->size =8;
		$edit->itv_placa->maxlength =12;
		$edit->itv_placa->db_name='v_placa';
		$edit->itv_placa->rel_id ='r_reciboit';

		$crea = '<a id="creai_<#i#>" href="javascript:creainmuebleid(<#i#>);" title="Agregar/modificar Inmueble">'.image('add.png','#',array("border"=>0)).'</a>';
		$edit->itid_inmueble = new inputField('id_inmueble','id_inmueble_<#i#>');
		$edit->itid_inmueble->rule='max_length[11]';
		$edit->itid_inmueble->size =3;
		$edit->itid_inmueble->maxlength =11;
		$edit->itid_inmueble->db_name='id_inmueble';
		$edit->itid_inmueble->rel_id ='r_reciboit';
		$edit->itid_inmueble->append($bINMUEBLE);
		$edit->itid_inmueble->append($crea);
		
		$edit->iti_catastro = new inputField('v_placa','i_catastro_<#i#>');
		$edit->iti_catastro->rule='max_length[12]';
		$edit->iti_catastro->size =6;
		$edit->iti_catastro->maxlength =12;
		$edit->iti_catastro->db_name='i_catastro';
		$edit->iti_catastro->rel_id ='r_reciboit';
		
		$crea = '<a id="creap_<#i#>" href="javascript:creapublicidadid(<#i#>);" title="Agregar/modificar Publicidad">'.image('add.png','#',array("border"=>0)).'</a>';
		$edit->itid_publicidad = new inputField('id_publicidad','id_publicidad_<#i#>');
		$edit->itid_publicidad->rule='max_length[11]';
		$edit->itid_publicidad->size =3;
		$edit->itid_publicidad->maxlength =11;
		$edit->itid_publicidad->db_name='id_publicidad';
		$edit->itid_publicidad->rel_id ='r_reciboit';
		$edit->itid_publicidad->append($bPUBLICIDAD);
		$edit->itid_publicidad->append($crea);
		
		$id     =$edit->get_from_dataobjetct('id');
		
		if($id>0){
			$id_abono = $this->datasis->dameval("SELECT b.id FROM r_abonosit a JOIN r_abonos b ON a.abono=b.id WHERE a.recibo=$id LIMIT 1");
			if($id_abono>0){
				$action = "javascript:location.href='" .site_url('recaudacion/r_abonos/dataedit/show/'.$id_abono). "'";
				$edit->button_status("add_r_abonos","Ver Cobranza $id",$action,"TL","show");
			}else{
				$action = "javascript:window.location='" .site_url($this->url.'/anular/'.$edit->rapyd->uri->get_edited_id()."'");
				$edit->button_status("btn_status",'Anular',$action,"TR","show");
				
				$action = "javascript:location.href='" .site_url('recaudacion/r_abonos/dataedit/'.$id.'/create'). "'";
				$edit->button_status("add_r_abonos","Cobrar",$action,"TR","show");
				
				$edit->buttons('modify','delete');
			}
		}
		
		$id_contribu = $edit->get_from_dataobjetct('id_contribu');
		$action      = "javascript:window.location='" .site_url($this->url.'dataedit/'.$id_contribu.'/create'."'");
		$edit->button_status("btn_crearsimiliar",'Agregar Similar' ,$action,"TL","show");
		
		$action = "javascript:analizar()";
		$edit->button_status("btn_statusam",'Analizar Contribuyente',$action,"TL","modify");
		$edit->button_status("btn_statusac",'Analizar Contribuyente',$action,"TL","create");

		$edit->buttons('add_rel','add', 'save', 'undo', 'back');
		$edit->build();
		
		$smenu['link']   =barra_menu('513');
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&$edit;
		$data['content'] = $this->load->view('recaudacion/r_recibo', $conten,true);
		$data['title']   = $this->tits.($this->cajan?" CAJA $this->cajan":"");
		$data["head"]    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}
	
	function _valida($do){
		$this->load->library('recaudacion');
		
		$error="";
		$id          = $do->get('id'         );
		$id_contribu = $do->get('id_contribu');
		$numero      = $do->get('numero'     );
		$fecha       = $do->get('fecha'      );
		
		$cerrado     = $this->datasis->dameval("SELECT COUNT(*) FROM r_cerrar WHERE fecha=$fecha");
		if($cerrado>0)
		$error.="<div class='alert' >Error. El Dia ya se encuetra Cerrado</div>";
		
		$contribu    =$this->datasis->damerow("SELECT rifci,nombre,id_parroquia,parroquia,id_zona,zona,dir1,dir2,dir3,dir4 FROM r_v_contribu WHERE id='$id_contribu' LIMIT 1");
		
		$do->set('nombre'        ,$contribu['nombre'         ] );
		$do->set('rifci'         ,$contribu['rifci'          ] );
		$do->set('id_parroquia'  ,$contribu['id_parroquia'   ] );
		$do->set('parroquia'     ,$contribu['parroquia'      ] );
		$do->set('id_zona'       ,$contribu['id_zona'        ] );
		$do->set('zona'          ,$contribu['zona'           ] );
		$do->set('dir1'          ,$contribu['dir1'           ] );
		$do->set('dir2'          ,$contribu['dir2'           ] );
		$do->set('dir3'          ,$contribu['dir3'           ] );
		$do->set('dir4'          ,$contribu['dir4'           ] );
		
		$intereses=$this->recaudacion->trae_conc_interes();
		
		
		/***********BORRAR INTERESES******************************/
		$temp=array();
		for($i=0;$i < $do->count_rel('r_reciboit');++$i){
			$requiere    = $do->get_rel('r_reciboit','requiere'   ,$i);
			if($requiere=='INTERESES'){
				//echo "SI";
				//array_splice($do->data_rel['r_reciboit'],$i,1);
			}else{
				$temp[]=$do->data_rel['r_reciboit'][$i];
			}
		}
		$do->data_rel['r_reciboit']=$temp;		
		
		//exit();
		/************** TRAE CONCEPTOS DESCUENTOS *****************/
		$descuentos=$this->recaudacion->trae_conc_descuento();
		
		/***********BORRAR DESCUENTOS******************************/
		for($i=0;$i < $do->count_rel('r_reciboit');$i++){
			$requiere    = $do->get_rel('r_reciboit','requiere'   ,$i);
			$id_conc     = $do->get_rel('r_reciboit','id_conc'    ,$i);
			if($requiere=='DESCUENTO'){
				array_splice($do->data_rel['r_reciboit'],$i,1);
			}
		}
		
		$total=0;$interes=0;
		for($i=0;$i < $do->count_rel('r_reciboit');$i++){
			/*echo "</br>"."requiere  :".*/$requiere   = $do->get_rel('r_reciboit','requiere'  ,$i);
			/*echo "</br>"."id_conc   :".*/$id_conc    = $do->get_rel('r_reciboit','id_conc'   ,$i);
			/*echo "</br>"."id_concit :".*/$id_concit  = $do->get_rel('r_reciboit','id_concit' ,$i);
			/*echo "</br>"."frecuencia:".*/$frecuencia = $do->get_rel('r_reciboit','frecuencia',$i);
			/*echo "</br>"."freval    :".*/$freval     = $do->get_rel('r_reciboit','freval'    ,$i);
			/*echo "</br>"."ano       :".*/$ano        = $do->get_rel('r_reciboit','ano'       ,$i);
			if($requiere=='INMUEBLE'){
				$id_inmueble = $do->get_rel('r_reciboit','id_inmueble',$i);
				if($id_inmueble>0){
					$inmueble = $this->datasis->damerow("SELECT * FROM r_v_inmueble WHERE id=$id_inmueble");
					
					$do->set_rel('r_reciboit','i_catastro'     ,$inmueble['catastro'    ],$i);
					$do->set_rel('r_reciboit','i_id_parroquia' ,$inmueble['id_parroquia'],$i);
					$do->set_rel('r_reciboit','i_parroquia'    ,$inmueble['parroquia'   ],$i);
					$do->set_rel('r_reciboit','i_id_zona'      ,$inmueble['id_zona'     ],$i);
					$do->set_rel('r_reciboit','i_zona'         ,$inmueble['zona'        ],$i);
					$do->set_rel('r_reciboit','i_dir1'         ,$inmueble['dir1'],$i);
					$do->set_rel('r_reciboit','i_dir2'         ,$inmueble['dir2'],$i);
					$do->set_rel('r_reciboit','i_dir3'         ,$inmueble['dir3'],$i);
					$do->set_rel('r_reciboit','i_dir4'         ,$inmueble['dir4'],$i);
					
					if($inmueble['id_contribu']!=$id_contribu)
						$error.="<div class='alert' >Error el Inmueble $id_inmueble no pertenece al contribuyente</div>";
				}else{
					$error.="<div class='alert' >Error. debe seleccionar un inmueble</div>";
				}
			}
			
			if($requiere=='VEHICULO'){
				$id_vehiculo = $do->get_rel('r_reciboit','id_vehiculo',$i);
				if($id_vehiculo>0){
				$vehiculo = $this->datasis->damerow("SELECT * FROM r_v_vehiculo WHERE id=$id_vehiculo");
				$do->set_rel('r_reciboit','v_placa'   ,$vehiculo['placa'    ],$i);
				$do->set_rel('r_reciboit','v_marca'   ,$vehiculo['marca'    ],$i);
				$do->set_rel('r_reciboit','v_modelo'  ,$vehiculo['modelo'   ],$i);
				
				if($vehiculo['id_contribu']!=$id_contribu)
					$error.="<div class='alert' >Error el Vehiculo $id_vehiculo no pertenece al contribuyente</div>";
				}else{
					$error.="<div class='alert' >Error. Debe seleccionar un Vehiculo</div>";
				}
			}
			
			if($requiere=='PUBLICIDAD'){
				$id_publicidad = $do->get_rel('r_reciboit','id_publicidad',$i);
				if($id_publicidad>0){
					$publicidad = $this->datasis->damerow("SELECT * FROM r_v_publicidad WHERE id=$id_publicidad");
					$do->set_rel('r_reciboit','id_publicidad'   ,$publicidad['id'          ],$i);
					$do->set_rel('r_reciboit','p_id_tipo'       ,$publicidad['id_tipo'     ],$i);
					$do->set_rel('r_reciboit','p_tipo_descrip'  ,$publicidad['descrip'     ],$i);
					$do->set_rel('r_reciboit','i_id_parroquia'  ,$publicidad['id_parroquia'],$i);
					$do->set_rel('r_reciboit','i_parroquia'     ,$publicidad['parroquia'   ],$i);
					$do->set_rel('r_reciboit','i_id_zona'       ,$publicidad['id_zona'     ],$i);
					$do->set_rel('r_reciboit','i_zona'          ,$publicidad['zona'        ],$i);
					$do->set_rel('r_reciboit','i_dir1'          ,$publicidad['dir1'        ],$i);
					$do->set_rel('r_reciboit','i_dir2'          ,$publicidad['dir2'        ],$i);
					$do->set_rel('r_reciboit','i_dir3'          ,$publicidad['dir3'        ],$i);
					$do->set_rel('r_reciboit','i_dir4'          ,$publicidad['dir4'        ],$i);
					
					if($publicidad['id_contribu']!=$id_contribu)
						$error.="<div class='alert' >Error. La publicidad $id_publicidad no pertenece al contribuyente</div>";
				}else{
					$error.="<div class='alert' >Error. Debe seleccionar un Vehiculo</div>";
				}
			}
			
			$monto = $do->get_rel('r_reciboit','monto',$i);
			$total+=$monto;
			
			$id_concit = $do->get_rel('r_reciboit','id_concit',$i);
			if(!($id_concit>0))
				$error.="Error. Debe Seleccionar un Concepto</br>";
			
			if($id_concit){
				$r_v_conc = $this->datasis->damerow("SELECT id_conc,partida,denopart,denomiconc,expira FROM r_v_conc WHERE id=$id_concit");
				$do->set_rel('r_reciboit','id_conc'       ,$r_v_conc['id_conc'],$i);
				$do->set_rel('r_reciboit','partida'       ,$r_v_conc['partida'],$i);
				$do->set_rel('r_reciboit','partida_denomi',$r_v_conc['denopart']     ,$i);
				$do->set_rel('r_reciboit','conc_denomi'   ,$r_v_conc['denomiconc']   ,$i);
				$do->set_rel('r_reciboit','expira'        ,$r_v_conc['expira'    ]   ,$i);		
			}
			
			//echo "</br>id_inmueble:".$id_inmueble;
			
			/* CALCULO DE INTERESES*/
			foreach($intereses as $k=>$v){
				$a                       = eval($intereses[$k]['formula']);
				$intereses[$k]['monto'] += $a;
			}
			
			/* CALCULO DE DESCUENTOS*/
			foreach($descuentos as $k=>$v){
				$a                        = eval($descuentos[$k]['formula']);
				$descuentos[$k]['formula'].":".$a."</br>"; 
				$descuentos[$k]['monto'] += $a;
			}
		}
		
		/*
		 * CREA ITEM DE INTERESES
		 */
		 
		 //print_r($intereses);
		 //exit();
		 
		foreach($intereses as $k=>$v){
			if($intereses[$k]['monto'] >0){
				$i++;
				$do->set_rel('r_reciboit','monto'          ,$intereses[$k]['monto'           ],$i);
				$do->set_rel('r_reciboit','id_conc'        ,$intereses[$k]['id_conc'         ],$i);
				$do->set_rel('r_reciboit','id_concit'      ,$intereses[$k]['id_concit'       ],$i);
				$do->set_rel('r_reciboit','denomi'         ,$intereses[$k]['denomi'          ],$i);
				$do->set_rel('r_reciboit','requiere'       ,$intereses[$k]['requiere'        ],$i);
				$do->set_rel('r_reciboit','partida'        ,$intereses[$k]['partida'         ],$i);
				$do->set_rel('r_reciboit','partida_denomi' ,$intereses[$k]['partida_denomi'  ],$i);
				$do->set_rel('r_reciboit','conc_denomi'    ,$intereses[$k]['conc_denomi'     ],$i);
				$total+=$intereses[$k]['monto' ];
			}
		}
		
		/*
		 * CREA ITEM DE DESCUENTOS
		 */
		foreach($descuentos as $k=>$v){
			if($descuentos[$k]['monto'] <0){
				$i++;
				$do->set_rel('r_reciboit','monto'          ,$descuentos[$k]['monto'           ],$i);
				$do->set_rel('r_reciboit','id_conc'        ,$descuentos[$k]['id_conc'         ],$i);
				$do->set_rel('r_reciboit','id_concit'      ,$descuentos[$k]['id_concit'       ],$i);
				$do->set_rel('r_reciboit','denomi'         ,$descuentos[$k]['denomi'          ],$i);
				$do->set_rel('r_reciboit','requiere'       ,$descuentos[$k]['requiere'        ],$i);
				$do->set_rel('r_reciboit','partida'        ,$descuentos[$k]['partida'         ],$i);
				$do->set_rel('r_reciboit','partida_denomi' ,$descuentos[$k]['partida_denomi'  ],$i);
				$do->set_rel('r_reciboit','conc_denomi'    ,$descuentos[$k]['conc_denomi'     ],$i);
				$total+=$descuentos[$k]['monto' ];
			}
		}
		
		if(empty($error)){
			$do->set('monto',$total);
			$user   = $this->session->userdata('usuario');
			$usere  = $this->db->escape($user);
			$caja   = $this->datasis->dameval("SELECT caja FROM r_caja JOIN  usuario ON r_caja.id=usuario.caja WHERE us_codigo =$usere");
			$do->set('caja',$caja);
			if(empty($numero)){
				if($caja>0){
					$query="SELECT id_contador FROM r_caja WHERE id=$caja";
					$id_contador=$this->datasis->dameval($query);
					$query="SELECT proxnumero FROM r_contador WHERE id=$id_contador";
					$n=$this->datasis->dameval($query);
					if($n>0){
						$do->set('numero',$n);
						$query="UPDATE r_contador SET proxnumero=1+proxnumero WHERE id =$id_contador";
						$this->db->query($query);
					}else{
						$do->set('numero',1);
						$query="UPDATE r_contador SET proxnumero=1 WHERE id =$id_contador";
						$this->db->query($query);
					}
				}else{
					$error.="<div class='alert' >Error. El Usuario no ha sido asignado como cajero</div>";
				}				
			}
		}
		
		
		if(!empty($error)){
			$do->error_string=$error;
			$do->error_message_ar['pre_ins']=$error;
			$do->error_message_ar['pre_upd']=$error;
			return false;
		}
	}
	
	function damedeuda(){
		$this->load->library('recaudacion');
		
		$id_contribu = $this->input->post('id_contribu');
		$tipo        = $this->input->post('tipo');
		$idreq       = $this->input->post('id_requiere');
		
		echo json_encode($this->recaudacion->damedeuda($id_contribu,$tipo,$idreq));
	}
	
	function damemonto(){
		$this->load->library('recaudacion');
		
		$id_concit   = $this->input->post('id_concit'  );
		$id          = $this->input->post('id'         );
		$id_contribu = $this->input->post('id_contribu');
		$base        = $this->input->post('base'       );
		
		$formula     = $this->datasis->damerow("SELECT formula,ano FROM r_concit WHERE id=$id_concit");
		echo $this->recaudacion->calculamonto($formula['formula'],$formula['ano'],$id,$id_contribu,$base);
	}
	
	function dameconc(){
		$this->load->library('recaudacion');
		
		echo json_encode($this->recaudacion->dameconc());
	}
	
	function dameinteres($base,$frecibo,$ano,$frecuencia,$freval,$modo=1){
		/*
		 * Modo 1: Calcula Intereses Sobre Intereses
		 * Modo 2: Calcula Sin Intereses Sobre intereses
		 * */
		
		$this->load->library('recaudacion');
		
		return $this->recaudacion->dameinteres($base,$frecibo,$ano,$frecuencia,$freval,$modo);
	}
	
	function anular($id){
		$error='';
		
		$ide     = $this->db->escape($id);
		$fecha   = $this->datasis->dameval("SELECT fecha FROM r_recibo WHERE id=$ide");
		$fechae  = $this->db->escape($fecha);
		$cerrado = $this->datasis->dameval("SELECT COUNT(*) FROM r_cerrar WHERE fecha=REPLACE($fechae,'-','')");
		
		if($cerrado>0)
		$error.="<div class='alert' >Error. El Dia ".dbdate_to_human($fecha)." ya se encuetra Cerrado</div>";
		
		if($id>0 && empty($error)){
			
			$id_abono = $this->datasis->dameval("SELECT b.id FROM r_abonosit a JOIN r_abonos b ON a.abono=b.id WHERE a.recibo=$id LIMIT 1");
			if($id_abono>0){
					$error .='El Recibo esta Cobrado, debe eliminar la cobranza primero';
			}else{
				$this->db->query("DELETE FROM r_reciboit WHERE id_recibo=$id");
				$this->db->query("UPDATE r_recibo SET id_contribu=-1, rifci='ANULADO',nombre='ANULADO',monto=0 WHERE id=$id ");
				$this->db->query("INSERT INTO r_reciboit (id,id_recibo,id_conc,id_concit,denomi,monto) VALUES('',$id,-1,-1,'ANULADO',0)");
			}
		}else{
			$error .='Faltan parametros';
		}
		
		if(empty($error)){
			logusu('r_recibo',"anulo recibo ref $id");
			redirect($this->url."/dataedit/show/".$id);
		}else{
			$error="<div class='alert'>".$error."</div>";
			logusu('r_recibo',"anulo recibo ref $id con error $error");
			$data['content'] = $error.anchor($this->url."/dataedit/show/$id",'Regresar');
			$data['title']   = " Recibos ";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}
	}
	
	function dataprint_solvencia($tipo='A',$st,$uid){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Imprimir Solvencia', 'r_recibo');
		//$id=$edit->get_from_dataobjetct('id');
		//$urlid=$edit->pk_URI();
		$id   =$uid;
		$urlid=$uid;
		$ide  =$this->db->escape($id);
		
		if($tipo=='A'){
			$R_RECIBO_IDCONCIT_SOLVENCIA_A = $this->datasis->traevalor("R_RECIBO_IDCONCIT_SOLVENCIA_A",26,"ID DE CONCEPTOIT DE SOLCENVIA A");
			$url=site_url('formatos/descargar/R_SOLVENCI/A/'.$urlid);
			$c = $this->datasis->dameval("SELECT COUNT(*) FROM r_reciboit WHERE id_concit=".$R_RECIBO_IDCONCIT_SOLVENCIA_A." AND id_recibo=$ide");
		}else{
			$R_RECIBO_IDCONCIT_SOLVENCIA_B = $this->datasis->traevalor("R_RECIBO_IDCONCIT_SOLVENCIA_B",16,"ID DE CONCEPTOIT DE SOLCENVIA B");
			$url=site_url('formatos/descargar/R_SOLVENCI/B/'.$urlid);
			$c = $this->datasis->dameval("SELECT COUNT(*) FROM r_reciboit WHERE id_concit=".$R_RECIBO_IDCONCIT_SOLVENCIA_B." AND id_recibo=$ide");
		}
		
		$edit->back_url = site_url($this->url.'dataedit/show/'.$uid);

		$edit->back_save   = true;
		$edit->back_delete = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		//$edit->on_save_redirect   = false;

		$edit->post_process('update','_post_print_solvencia_update');

		//$edit->container = new containerField('impresion','La descarga se realizara en 1 segundos, en caso de no hacerlo haga click '.anchor('formatos/descargar/R_SOLVENCIA/'.$urlid,'aqui'));

		if($tipo=='A' && $c>0){
			$edit->solvencia = new inputField('Solvencia N&uacute;mero A','solvencia');
			$edit->solvencia->rule        ='max_length[12]|required';
			$edit->solvencia->size        =14;
			$edit->solvencia->maxlength   =12;
			$edit->solvencia->autocomplete=false;
		}elseif($tipo=='B' && $c>0){
			$edit->solvenciab = new inputField('Solvencia N&uacute;mero B','solvenciab');
			$edit->solvenciab->rule        ='max_length[12]|required';
			$edit->solvenciab->size        =14;
			$edit->solvenciab->maxlength   =12;
			$edit->solvenciab->autocomplete=false;
		}
		
		$edit->numero = new inputField('Recibo N&uacute;mero','numero');
		$edit->numero->rule        ='max_length[12]|required';
		$edit->numero->size        =14;
		$edit->numero->maxlength   =12;
		$edit->numero->autocomplete=false;
		$edit->numero->mode='autohide';

		$edit->id = new inputField('Ref','numero');
		$edit->id->rule='max_length[8]';
		$edit->id->mode='autohide';
		$edit->id->size =10;
		$edit->id->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule = 'chfecha';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength =8;
		
		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='required|max_length[200]';
		$edit->nombre->size =50;
		$edit->nombre->maxlength =200;
		$edit->nombre->autocomplete=false;
		$edit->nombre->mode='autohide';

		$edit->rifci = new inputField('Rif/CI','rifci');
		$edit->rifci->rule      ='required|max_length[13]';
		$edit->rifci->size      =15;
		$edit->rifci->maxlength =13;
		$edit->rifci->mode='autohide';

		$edit->monto = new inputField('Monto','monto');
		$edit->monto->rule='max_length[12]|numeric';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =14;
		$edit->monto->showformat='decimal';
		$edit->monto->mode='autohide';
		$edit->monto->maxlength =12;

		if($c>0)
		$edit->buttons('save');
		$edit->buttons( 'undo','back');
		$edit->build();

		if($c>0){
			$script= '<script type="text/javascript" >
			$(function() {
				setTimeout(\'window.location="'.$url.'"\',01);
			});
			</script>';
			$title='IMPRIMIR SOLVENCIA '.$tipo;
		}else{
				$script='';
				$title ='ERROR. DEBE PAGAR LA SOLVENCIA TIPO '.$tipo.' PARA IMPRIMIRLA';
		}

		
		$data['content'] = "<div class='alert'>".'ERROR. DEBE PAGAR LA SOLVENCIA TIPO '.$tipo.' PARA IMPRIMIRLA'."</div>".$edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css');
		$data['script'] .= $script;
		$data['title']   = $title;
		$this->load->view('view_ventanas', $data);
	}
	
	function damecxc(){
		$id_contribu = $this->input->post('id_contribu');
		$id_cxc      = $this->input->post('id_cxc'     );
		$id_contribue=$this->db->escape($id_contribu   );
		$id_cxce     =$this->db->escape($id_cxc        );
		
		$query="
		SELECT r_cxcit.id_conc,r_cxcit.id id_cxcit,r_cxcit.id_concit id,r_cxcit.ano,r_cxcit.acronimo,r_cxcit.denomi ,r_cxcit.requiere,r_cxcit.id_inmueble,r_cxcit.i_catastro catastro,r_cxcit.frecuencia,r_cxcit.freval
		,r_cxcit.id_vehiculo,r_cxcit.v_placa,r_cxc.id_contribu,r_cxcit.observa AS observa,r_cxcit.base,r_cxcit.monto,r_cxcit.modo,r_cxcit.id_publicidad
		FROM r_cxc
		JOIN r_cxcit ON r_cxc.id=r_cxcit.id_cxc
		LEFT JOIN r_reciboit ON r_cxcit.id=r_reciboit.id_cxcit
		LEFT JOIN r_recibo ON r_reciboit.id_recibo=r_recibo.id 
		LEFT JOIN r_abonosit ON r_recibo.id=r_abonosit.recibo 
		LEFT JOIN r_abonos ON r_abonos.id=r_abonosit.abono 
		WHERE r_reciboit.id IS NULL 
		";
		
		if($id_contribu && $id_contribu!='null')
		$query.=" AND r_cxc.id_contribu=$id_contribue ";
		
		if($id_cxc && $id_cxc!='null'){
			
			$id_contribu = $this->datasis->dameval("SELECT id_contribu FROM r_cxc WHERE r_cxc.id=$id_cxce");
			$id_contribue=$this->db->escape($id_contribu );
			$query      .=" AND (r_cxc.id=$id_cxce OR (r_cxc.id_contribu=$id_contribue AND r_cxcit.expira='N' ) )";
		}
		
		$mSQL   = $this->db->query($query);
		$arreglo= $mSQL->result_array($query);
		
		foreach($arreglo as $key=>$value)
			foreach($value as $key2=>$value2) 
			$arreglo[$key][$key2] = ($value2);

		echo json_encode($arreglo);
	}
	
	function _pre_delete($do){
		$error="";
		$fecha   = $do->get('fecha' );
		$fechae  = $this->db->escape($fecha);
		$cerrado = $this->datasis->dameval("SELECT COUNT(*) FROM r_cerrar WHERE fecha=REPLACE($fechae,'-','')");
		
		if($cerrado>0)
		$error.="<div class='alert' >Error. El Dia ".dbdate_to_human($fecha)." ya se encuetra Cerrado</div>";
		
		if(!empty($error)){
			$do->error_string=$error;
			$do->error_message_ar['pre_del']=$error;
			return false;
		}
	}
	
	function inmueble_cant(){
		$this->load->library('recaudacion');
		$id_contribu = $this->input->post('id_contribu');
		echo $this->recaudacion->inmueble_cant($id_contribu);
	}
	
	function inmueble_get(){
		$this->load->library('recaudacion');
		$id_contribu = $this->input->post('id_contribu');
		echo json_encode($this->recaudacion->inmueble_get($id_contribu));
	}
	
	function vehiculo_cant(){
		$this->load->library('recaudacion');
		$id_contribu = $this->input->post('id_contribu');
		echo $this->recaudacion->vehiculo_cant($id_contribu);
	}
	
	function vehiculo_get(){
		$this->load->library('recaudacion');
		$id_contribu = $this->input->post('id_contribu');
		echo json_encode($this->recaudacion->vehiculo_get($id_contribu));
	}
	
	function _post_print_solvencia_update($do){
		$id =$do->get('id');
		redirect($this->url."dataedit/show/$id");
	}
	
	function ultimo_pago($id_conc,$id_inmueble=null,$id_vehiculo=null){
		$this->load->library('recaudacion');
		
		return $this->recaudacion->ultimo_pago($id_conc,$id_inmueble,$id_vehiculo);
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		
		$sal ='';
		$sal.= " numero:".$do->get("numero");
		$sal.= " fecha:".$do->get("fecha" );
		$sal.= " rifci:" .$do->get("rifci" );
		$sal.= " nombre:".$do->get("nombre");
		$sal.= " monto:" .$do->get("monto" );
		
		//292344
		logusu($do->table,"Creo $this->tits $primary $sal ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		
		$sal ='';
		$sal.= " numero:".$do->get("numero");
		$sal.= " fecha:".$do->get("fecha" );
		$sal.= " rifci:" .$do->get("rifci" );
		$sal.= " nombre:".$do->get("nombre");
		$sal.= " monto:" .$do->get("monto" );
		
		logusu($do->table,"Modifico $this->tits $primary $sal");
	}
	function _post_delete($do){
		$primary =implode(',',$do->pk);
		$sal ='';
		$sal.= " numero:".$do->get("numero");
		$sal.= " fecha:".$do->get("fecha" );
		$sal.= " rifci:" .$do->get("rifci" );
		$sal.= " nombre:".$do->get("nombre");
		$sal.= " monto:" .$do->get("monto" );
		
		logusu($do->table,"Elimino $this->tits $primary $sal");
	}

	function instalar(){
		$mSQL="CREATE TABLE `r_recibo` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `id_contribu` int(11) NOT NULL,
		  `fecha` date NOT NULL,
		  `rifci` varchar(12) DEFAULT NULL,
		  `nombre` varchar(100) DEFAULT NULL,
		  `telefono` varchar(50) DEFAULT NULL,
		  `id_parroquia` int(11) DEFAULT NULL,
		  `parroquia` varchar(100) DEFAULT NULL,
		  `id_zona` int(11) DEFAULT NULL,
		  `zona` varchar(100) DEFAULT NULL,
		  `dir1` varchar(255) DEFAULT NULL,
		  `dir2` varchar(255) DEFAULT NULL,
		  `dir3` varchar(255) DEFAULT NULL,
		  `dir4` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		$this->db->simple_query($mSQL);
		
		$query="
		CREATE TABLE `r_reciboit` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`id_recibo` INT(11) NULL DEFAULT NULL,
			`id_concit` INT(11) NULL DEFAULT NULL,
			`id_conc` INT(11) NULL DEFAULT NULL,
			`id_vehiculo` INT(11) NULL DEFAULT NULL,
			`id_inmueble` INT(11) NULL DEFAULT NULL,
			`ano` INT(11) NULL DEFAULT NULL,
			`monto` DECIMAL(19,2) NULL DEFAULT '0.00',
			`observa` VARCHAR(255) NULL DEFAULT NULL,
			`acronimo` VARCHAR(50) NOT NULL,
			`denomi` VARCHAR(80) NOT NULL,
			`i_id_parroquia` INT(11) NULL DEFAULT NULL,
			`i_parroquia` VARCHAR(100) NULL DEFAULT NULL,
			`i_id_zona` INT(11) NULL DEFAULT NULL,
			`i_zona` VARCHAR(100) NULL DEFAULT NULL,
			`i_dir1` VARCHAR(255) NULL DEFAULT NULL,
			`i_dir2` VARCHAR(255) NULL DEFAULT NULL,
			`i_dir3` VARCHAR(255) NULL DEFAULT NULL,
			`i_dir4` VARCHAR(255) NULL DEFAULT NULL,
			`v_placa` VARCHAR(12) NULL DEFAULT NULL,
			`i_catastro` VARCHAR(20) NULL DEFAULT NULL,
			`requiere` VARCHAR(20) NULL DEFAULT NULL,
			`partida` VARCHAR(20) NULL DEFAULT NULL,
			`v_marca` VARCHAR(50) NULL DEFAULT NULL,
			`v_modelo` VARCHAR(50) NULL DEFAULT NULL,
			`partida_denomi` VARCHAR(100) NULL DEFAULT NULL,
			`conc_denomi` VARCHAR(100) NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		AUTO_INCREMENT=1
		";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `r_recibo` 	ADD COLUMN `caja` INT NULL DEFAULT NULL";
		$this->db->simple_query($mSQL);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `numero` VARCHAR(12) NULL DEFAULT NULL ";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `monto` DECIMAL(19,2) NULL DEFAULT '0'";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `razon` VARCHAR(255) NULL DEFAULT NULL";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `solvencia` VARCHAR(10) NULL DEFAULT NULL ";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `solvenciab` VARCHAR(10) NULL DEFAULT NULL ";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_recibo` ADD COLUMN `licores` VARCHAR(10) NULL DEFAULT NULL";
		$this->db->simple_query($query);
		$query="
		CREATE TABLE `r_otrospagos` (
			`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`numero` VARCHAR(15) NULL DEFAULT NULL,
			`fecha` DATE NULL DEFAULT NULL,
			`rifci` VARCHAR(20) NULL DEFAULT NULL,
			`nombre` VARCHAR(255) NULL DEFAULT NULL,
			`concepto` VARCHAR(255) NULL DEFAULT NULL,
			`monto` DECIMAL(19,2) NULL DEFAULT '0.00',
			`observa` TEXT NULL,
			PRIMARY KEY (`id`)
		)
		ENGINE=MyISAM
		";
		$this->db->simple_query($query);
		
		$query="ALTER TABLE `r_otrospagos` 	ADD INDEX `rifci` (`rifci`)";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `id_cxcit` INT(11) NULL DEFAULT NULL AFTER `id_conc`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD INDEX `id_cxcit` (`id_cxcit`)";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `frecuencia` SMALLINT NULL DEFAULT '0' AFTER `ano`   ";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit`ADD COLUMN `freval` SMALLINT NULL DEFAULT NULL AFTER `frecuencia`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `base` DECIMAL(19,2) NULL DEFAULT '0' AFTER `freval`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `modo` VARCHAR(10) NULL DEFAULT NULL AFTER `requiere`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `id_publicidad` INT(11) NULL DEFAULT NULL AFTER `id_inmueble`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `p_id_tipo` INT NULL DEFAULT NULL AFTER `conc_denomi`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `p_tipo_descrip` VARCHAR(100) NULL DEFAULT NULL AFTER `p_id_tipo`";
		$this->db->simple_query($query);
		$query="ALTER TABLE `r_reciboit` ADD COLUMN `expira` CHAR(1) NULL DEFAULT NULL";
		$this->db->simple_query($query);
	}
}
?>
