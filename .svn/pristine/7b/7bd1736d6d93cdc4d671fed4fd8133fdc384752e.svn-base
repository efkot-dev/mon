<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(isset($id) && $id>0){

}
?>
<STYLE>
.getbattery {
    padding: 0 5px 10px 0;
}
.getbattery .getname {
    background: #dbe3e9;
    border-radius: 5px;
    padding: 1px 10px;
	margin: 0px 0 5px 0;
}
.getbattery .getlist {
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    width: 100%;
    justify-content: flex-start;
    align-items: flex-start;
}
.getbattery .getlist .getakb {
    width: 100%;
    display: flex;
    flex-direction: row;
    margin: 0px 0 0 5px;
    justify-content: flex-start;
    align-items: center;
}
.getbattery .getlist .getakb .tech .a {
    line-height: 13px;
    height: 14px;
    font-size: 14px;
	color:grey;
}
.getbattery .getlist .getakb .tech .v {
    line-height: 13px;
    height: 14px;
    font-size: 17px;
}
.getbattery .getlist .getakb .tech {
    display: flex;
    flex-direction: column;
    width: 50px;
    justify-content: center;
    align-items: flex-start;
    padding: 5px 0 0 5px;
}
.getbattery .getlist .getakb .techimg {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.getbattery .getlist .getakb .techimg img {
    height: 38px;
}
</STYLE>
<div class="getbattery">
<div class="getname">Підключені акумулятори</div>
	<div class="getlist">
		<div class="getakb">		
			<span class="techimg">
				<img src="../style/img/getbattery.png">
			</span>
			<span class="tech">
				<span class="v">12V</span>
				<span class="a">100A</span>
			</span>
		</div>			
		<div class="getakb">		
			<span class="techimg">
				<img src="../style/img/getbattery.png">
			</span>
			<span class="tech">
				<span class="v">12V</span>
				<span class="a">100A</span>
			</span>
		</div>		

	</div>
</div>