function home_load_content(){

	//Load all the function for everyBlock !
	
	//Home date
	home_load_content_tableau_de_bord();
	
	//Transactions
	home_load_content_transaction_comptes_client();
	home_load_content_transaction_leads_a();
	home_load_content_transaction_leads_f();
	home_load_content_transaction_commandes();
	
	//Produits
	home_load_content_products_produits_actifs();
	home_load_content_products_references_actives();
	home_load_content_products_familles3();
	
	//Partenaires
	home_load_content_advertisers_annonceurs();
	home_load_content_advertisers_fournisseurs();
	home_load_content_advertisers_annonceurs_non_factures();
	home_load_content_advertisers_prospects();
	home_load_content_advertisers_annonceurs_bloques();
	home_load_content_advertisers_annonceurs_litige();	
}

//Function to load the loader !
function load_small_loader_here(block){
	//document.getElementById(block).innerHTML	= '<img src="ressources/images/Circle Ball.gif" alt="Chargement en cours.." title="Chargement en cours.." width="52" />';
	
	//document.getElementById(block).innerHTML	= '<img src="ressources/images/loading.gif" alt="Chargement en cours.." title="Chargement en cours.." width="52" />';
	
	document.getElementById(block).innerHTML	= '<i class="fa fa-spinner fa-pulse"></i>';
	
}

/***************************************************************************/
/********************* Start Tableau de bord *******************************/
/***************************************************************************/
function home_load_content_tableau_de_bord(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-tableau-de-bord.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_tableau_de_bord').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_tableau_de_bord').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

/***************************************************************************/
/********************* End Tableau de bord *******************************/
/***************************************************************************/

/***************************************************************************/
/********************* Start Transaction **********************************/
/***************************************************************************/
function home_load_content_transaction_comptes_client(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-transaction-comptes-clients.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_tran_comptesclients').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_tran_comptesclients').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_transaction_leads_a(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-transaction-leadsa.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_tran_leadsa').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_tran_leadsa').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_transaction_leads_f(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-transaction-leadsf.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_tran_leadsf').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_tran_leadsf').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_transaction_commandes(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-transaction-commandes.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_tran_commandes').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_tran_commandes').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}
/***************************************************************************/
/********************* End Transaction **********************************/
/***************************************************************************/

/***************************************************************************/
/********************* Start Produits **************************************/
/***************************************************************************/
function home_load_content_products_produits_actifs(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-products-produits-actifs.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_pro_produits_actifs').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_pro_produits_actifs').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}


function home_load_content_products_references_actives(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-products-references-actives.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_pro_references_actives').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_pro_references_actives').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_products_familles3(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-products-famille3.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_pro_familles3').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_pro_familles3').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}


/***************************************************************************/
/********************* End Produits ****************************************/
/***************************************************************************/


/***************************************************************************/
/********************* Start Partenaires ***********************************/
/***************************************************************************/
function home_load_content_advertisers_annonceurs(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-annonceurs.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_annonceurs').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_annonceurs').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}


function home_load_content_advertisers_fournisseurs(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-fournisseurs.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_fournisseurs').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_fournisseurs').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_advertisers_annonceurs_non_factures(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-annonceurs-non-factures.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_annonceurs_n_factur').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_annonceurs_n_factur').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_advertisers_prospects(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-prospects.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_prospects').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_prospects').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_advertisers_annonceurs_bloques(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-annonceurs-bloques.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_annonceurs_bloq').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_annonceurs_bloq').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function home_load_content_advertisers_annonceurs_litige(){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/home-stats-load-advertisers-annonceurs-litige.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){

				if(OAjax.responseText !=''){
					document.getElementById('hb_adv_annonceurs_litige').innerHTML =''+OAjax.responseText+'';
					
					//Initiate the Angular
					//$scope.$apply();
					//eval(document.getElementById('home_center_content'));
					//$state.reload();
					
				}else{
					document.getElementById('hb_adv_annonceurs_litige').innerHTML ='Erreur chargement !';
					//alert('Erreur, veuillez recharcher la page !');
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

/***************************************************************************/
/********************* End Partenaires *************************************/
/***************************************************************************/
