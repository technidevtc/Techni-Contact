<?php
?>
<script type="text/javascript">
<!--
var folder_list = new Array();

function getFamilies(params)
{
	//alert('FamiliesExplore.php?<?php echo $sid ?>' + params);
	makeRequest('FamiliesExplore.php?<?php echo $sid ?>' + params + '&products_filter=' + products_filter, 'ProcessFamilies');
}

function ProcessFamilies(response)
{
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				var cols = response.responseText.split('<col_separator_p4le1iazia8rLab8>');
				//alert(cols[0]);
				if (cols[0] == '__FAMILIES_DYNAMIC__')
				{
					document.getElementById('PEFcolg').innerHTML = cols[1];
					document.getElementById('PEFcolc').innerHTML = cols[2];
					// Changement du comportement des tag a
					var divs = document.getElementById('PEFcolg').getElementsByTagName('div');
					folder_list = new Array();
					for (i = 0; i <  divs.length; i++)
					{
						if (divs[i].className == 'sf')
						{
							a = divs[i].firstChild;
							aurl = a.href.split('/');
							a.family_id = parseInt(aurl[aurl.length-1]);
							folder_list[a.family_id] = a.innerHTML;
							a.onclick = function () { unfold(this.family_id); return false; }
							
							as = document.getElementById('folder'+a.family_id+'_s').getElementsByTagName('a');
							for (j = 0; j < as.length; j++)
							{
								aurl = as[j].href.split('/');
								as[j].family_ref_name = aurl[aurl.length-1];
								as[j].onclick = function ()	{ getFamilies('&family_ref_name=' + this.family_ref_name); return false; }
							}
		
						}
						else if (divs[i].className == 'ssf')
						{
							as = divs[i].getElementsByTagName('a');
							for (j = 0; j < as.length; j++)
							{
								aurl = as[j].href.split('/');
								as[j].family_ref_name = aurl[aurl.length-1];
								as[j].onclick = function ()	{ getFamilies('&family_ref_name=' + this.family_ref_name); return false; }
							}
						}
						else continue;
					}
					// Changement du comportement des tag a du la page principale
					
					if (cols[3]) unfold(parseInt(cols[3]));
					else foldall();
				}
				else if (cols[0] == '__FAMILIES_FIXED__')
				{
					document.getElementById('PEFcolg').innerHTML = cols[1];
					document.getElementById('PEFcolc').innerHTML = cols[2];
					var as = document.getElementById('PEFcolg').getElementsByTagName('a');
					for (i = 0; i <  as.length; i++)
					{
						aurl = as[i].href.split('/');
						if (aurl[aurl.length-2] == 'familles')
						{
							link = aurl[aurl.length-1].split(',');
							
							as[i].family_ref_name = link[0];
							as[i].family_page = link[1] ? link[1] : 1;
							as[i].onclick = function ()	{ getFamilies('&family_ref_name=' + this.family_ref_name + '&page=' + this.family_page); return false; }
						}
					}
					
					as = document.getElementById('PEFcolc').getElementsByTagName('a');
					for (i = 0; i <  as.length; i++)
					{
						aurl = as[i].href.split('/');
						if (aurl[aurl.length-2] == 'familles')
						{
							link = aurl[aurl.length-1].split(',');
							
							as[i].family_ref_name = link[0];
							as[i].family_page = link[1] ? link[1] : 1;
							as[i].onclick = function ()	{ getFamilies('&family_ref_name=' + this.family_ref_name + '&page=' + this.family_page); return false; }
						}
						else if (aurl[aurl.length-2] == 'produits')
						{
							pdt_refs = aurl[aurl.length-1].split('-');
							as[i].family_id = pdt_refs[0];
							as[i].product_id = pdt_refs[1];
							//as[i].product_ref_name = pdt_refs[2];
							as[i].onclick = function ()	{ getFamilies('&family_ID=' + this.family_id + '&product_ID=' + this.product_id); return false; }
						}
					}
				}
				else if (cols[0] == '__ERROR__')
				{
					document.getElementById('PEFcolc').innerHTML = cols[1];
				}

			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById('PerfReqLabelProducts').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}
}

function unfold(id)
{
	var k = 0;
	var id_prev = 0;
	for (var i in folder_list)
	{
		if (id == i)
		{
			document.getElementById('folder'+i+'_u').style.display = 'block';
			document.getElementById('folder'+i+'_f').style.display = 'none';
			document.getElementById('folder'+i+'_s').style.display = 'block';
			document.getElementById('folder'+i+'_s').parentNode.className = 'elt';
			document.getElementById('folder'+i+'_s').parentNode.style.display = 'block';
		}
		else
		{
			document.getElementById('folder'+i+'_u').style.display = 'none';
			document.getElementById('folder'+i+'_f').style.display = 'block';
			document.getElementById('folder'+i+'_s').style.display = 'none';
			if (k%2 != 1 || id_prev != id) document.getElementById('folder'+i+'_s').parentNode.style.display = 'none';
		}
		id_prev = i;
		k++;
	}
	/*document.getElementById('navig').innerHTML = '<a href="javascript: foldall()">' + document.getElementById('folder'+id+'_u').parentNode.getElementsByTagName('div')[0].innerHTML + '</a> &raquo; <h1>' + folder_list[id] + '</h1>';*/
}

function foldall()
{
	var k = 0;
	for (var i in folder_list)
	{
		document.getElementById('folder'+i+'_u').style.display = 'none';
		document.getElementById('folder'+i+'_f').style.display = 'block';
		document.getElementById('folder'+i+'_s').style.display = 'block';
		if (k%2 == 0) document.getElementById('folder'+i+'_s').parentNode.style.display = 'block';
		if (k%4 == 2) document.getElementById('folder'+i+'_s').parentNode.className = 'eltDeux';
		k++;
	}
	/*document.getElementById('navig').innerHTML = "<h1>Cat&eacute;gories disponible dans l'espace &quot;<?php echo to_entities($top_family['name']) ?>&quot; : </h1>";*/
}


function initPEFamilyMenu()
{
	as = document.getElementById('menu').getElementsByTagName('a');
	for (i = 0; i < as.length; i++)
	{
		aurl = as[i].href.split('/');
		as[i].family_ref_name = aurl[aurl.length-1];
		//as[i].family_selected = false;
		as[i].onclick = function ()	{
			if (!this.family_selected)
			{
				//this.className = 'selected';
				getFamilies('&family_ref_name=' + this.family_ref_name);
				//this.family_selected = true;
			}
			return false;
		}
	}
	
	as = document.getElementById('PEFcolg').getElementsByTagName('a');
	for (i = 0; i < as.length; i++)
	{
		aurl = as[i].href.split('/');
		as[i].family_ref_name = aurl[aurl.length-1];
		//as[i].family_selected = false;
		as[i].onclick = function ()	{
			if (!this.family_selected)
			{
				//this.className = 'selected';
				getFamilies('&family_ref_name=' + this.family_ref_name);
				//this.family_selected = true;
			}
			return false;
		}
	}
}

//-->
</script>
			<div class="PELayer" id="ProductExplorerFamilyLayer">
				<div id="menu">
<?php
$menu_families = '';
$result = & $handle->query("select fr.id, fr.name, fr.ref_name from families f, families_fr fr where f.idParent = 0 and f.id = fr.id order by fr.id", __FILE__, __LINE__);
while ($top_fam = & $handle->fetchAssoc($result))
	$menu_families .= '<a href="familles/' . $top_fam['ref_name'] .'">' . to_entities($top_fam['name']) . '</a> ';

?>			
					<?php echo $menu_families ?>
				</div>
				<div id="PEFcolg">
					<div class="titre">Cat&eacute;gories</div>
					<div class="sf"><?php echo $menu_families ?></div>
				</div>
				<div id="PEFcolc">
				</div>
			</div>
<script type="text/javascript">initPEFamilyMenu();</script>
