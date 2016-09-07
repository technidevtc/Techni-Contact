function swap_cbtn(img, action)
{
	switch (action)
	{
		case 'out': if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif') { img.src = __ADMIN_URL__ + 'ressources/window_close.gif'; } break;
		case 'down': img.src = __ADMIN_URL__ + 'ressources/window_close_down.gif'; break;
		case 'up':
			if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif')
			{
				img.src = __ADMIN_URL__ + 'ressources/window_close.gif';
				eval('Hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default: break;
	}
}

function ShowFamiliesSearchDB()
{
	if (document.getElementById('FamiliesSearchDB').style.display != 'inline')
	{
		document.getElementById('FamiliesSearchDB').style.display = 'inline';
		document.getElementById('FamiliesSearchDBShad').style.display = 'inline';
		document.getElementById('FamiliesResultsDB').style.display = 'inline';
		document.getElementById('FamiliesResultsDBShad').style.display = 'inline';
		document.getElementById('FamiliesResultsDBShad').style.height = document.getElementById('FamiliesResultsDB').offsetHeight + 'px';
	}
}

function HideFamiliesSearchDB()
{
	document.getElementById('FamiliesSearchDBShad').style.display = 'none';
	document.getElementById('FamiliesSearchDB').style.display = 'none';
	document.getElementById('FamiliesResultsDBShad').style.display = 'none';
	document.getElementById('FamiliesResultsDB').style.display = 'none';
}

function ShowFamiliesResultsDB()
{
	ShowFamiliesSearchDB();
}

function HideFamiliesResultsDB()
{
	HideFamiliesSearchDB();
}

function FindFamilies()
{
	/*alert('FamiliesSearch.php?' + __SID__ +
	'&FamiliesSearchText=' + escape(document.getElementById('FamiliesSearchText').value) +
	'&FamiliesBeginBy=' + document.getElementById('FamiliesBeginBy').checked +
	'&FamiliesCaseSensitive=' + document.getElementById('FamiliesCaseSensitive').checked);*/
	
	makeRequest('FamiliesSearch.php?' + __SID__ +
	'&FamiliesSearchText=' + escape(document.getElementById('FamiliesSearchText').value) +
	'&FamiliesBeginBy=' + document.getElementById('FamiliesBeginBy').checked +
	'&FamiliesCaseSensitive=' + document.getElementById('FamiliesCaseSensitive').checked,
	'ProcessFamiliesSearchResults');
	
}

function ProcessFamiliesSearchResults(response)
{
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				document.getElementById('PerfReqF').style.visibility = 'hidden';
				mainsplit = response.responseText.split(__MAIN_SEPARATOR__);
				
				var fr = document.getElementById('FamiliesResults');
				var fre = document.getElementById('FamiliesResultsError');
				
				fr.style.height = 'auto'
				
				if (mainsplit[0] == '')
				{
					var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
					for (var i = 0; i < outputs.length-1; i++)
					{
						var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
						if (outputID.length == 2)
						{
							switch (outputID[0])
							{
								case 'FamiliesResults' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									
									if (data.length >= 3)
									{
										fresults = new Array();
										for (var j=0, k=0; j < data.length-1; j+=3, k++) fresults[k] = { "id" : data[j], "ref_name" : data[j+1], "name" : data[j+2] };
										if (fresults.length > 5) fr.style.height = '95px'; else fr.style.height = 'auto';
										
										var oiHTML = '<table cellpadding="0" cellspacing="0" border="0" width="' + ((Math.floor((fresults.length-1)/5)+1) * 250) + '"><tr>';
										var bgcolor = 'DBE0E7'; //E9EFF8
										for (var j=0; j < fresults.length; j++)
										{
											if (j%5 == 0)
											{
												if (bgcolor == 'F6F8FC') bgcolor = 'DBE0E7'; else bgcolor = 'F6F8FC';
												oiHTML += '<td style="width: 250px; background-color: #' + bgcolor + '">';
											}
											oiHTML += '<a href="#' + fresults[j]["ref_name"] + '" onclick="SelectFam(' + fresults[j]["id"] + '); return false;">' + fresults[j]["name"] + '</a><br />';
											if (j%5 == 4) oiHTML += '</td>';
										}
										if (j%5 != 4) oiHTML += '</div>';
										oiHTML += '</tr></table>';
										fr.innerHTML = oiHTML;
										fre.innerHTML = '';
										fr.style.display = 'block';
										fre.style.display = 'none';
									}
									else
									{
										fr.innerHTML = '';
										fre.innerHTML = 'Aucun résultat';
										fr.style.display = 'none';
										fre.style.display = 'block';
									}
									
									document.getElementById('FamiliesResultsDBShad').style.height = document.getElementById('FamiliesResultsDB').offsetHeight + 'px';
									
									break;
								
								default:
									document.getElementById(outputID[0]).innerHTML = outputID[1];
							}
						}
					}
				}
				else
				{
					var errors = mainsplit[0].split(__ERROR_SEPARATOR__);
					for (var i = 0; i < errors.length-1; i++)
					{
						var errorID = errors[i].split(__ERRORID_SEPARATOR__);
						if (errorID.length == 2 && errorID[0] == 'FamiliesResultsError')
						{
							fr.innerHTML = '';
							fre.innerHTML = errorID[1];
							fr.style.display = 'none';
							fre.style.display = 'block';
							document.getElementById('FamiliesResultsDBShad').style.height = document.getElementById('FamiliesResultsDB').offsetHeight + 'px';
						}
					}
				}
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById('PerfReqF').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}

}

function AlterFamilies(fieldlist)
{
	makeRequest('FamiliesAlter.php?' + __SID__ + fieldlist, 'ProcessFamiliesChanges');
}

function sort_ref_name(a, b)
{
	if (families[a][ref_name] > families[b][ref_name]) return 1;
	if (families[a][ref_name] < families[b][ref_name]) return -1;
	return 0;
}

function ShowSubFamiliesOptionsList(fidpp, fidp_s, fidpp_s)
{
	var s = document.fam.editParentValue;
	s.options.length = families[fidpp][nbchildren];
	for (var l = 0; l < families[fidpp][nbchildren]; l++)
	{
		s.options[l].value = families[fidpp][children][l];
		s.options[l].text  = families[families[fidpp][children][l]][name];
	}
	if (fidpp == fidpp_s) s.options.value = fidp_s;
}

function SelectFam(fid)
{
	var fidr = fid;
	var famTree = new Array();
	var n = 0;
	while (fidr != 0)
	{
		famTree[n++] = fidr;
		fidr = families[fidr][idParent];
	}
	famTree.reverse();
	var sfam;
	for (var i = 0; i < n; i++)
	{
		switch (i)
		{
			case 0:
				var af = document.getElementById('menu').getElementsByTagName('a');
				var j = 0;
				while (j < af.length && af[j].family_id != famTree[0]) j++;
				if (j != af.length) af[j].Select();
				break;

			case 1:
				var asf = document.getElementById('colg_sf').getElementsByTagName('a');
				var j = 0;
				while (j < asf.length && asf[j].family_id != famTree[1]) j++;
				if (j != asf.length)
				{
					asf[j].InitSSF();
					sfam = asf[j];
				}
				break;
				
			case 2:
				if (sfam)
				{
					var assf = sfam.ssfNext.getElementsByTagName('a');
					var j = 0;
					while (j < assf.length && assf[j].family_id != famTree[2]) j++;
					if (j != assf.length) assf[j].onclick();
				}
				break;
			
			default : break;
		}
	}
}

function addChild(fidParent, fid) {
	families[fidParent][children].push(fid);
	families[fidParent][nbchildren]++;
}

function delChild(fidParent, fid) {
	var i = 0;
	while (families[fidParent][children][i] != fid) i++;
	for (k = i; k < families[fidParent][nbchildren]-1; k++) families[fidParent][children][k] = families[fidParent][children][k+1];
	families[fidParent][children].pop();
	families[fidParent][nbchildren]--;
}

function ProcessFamiliesChanges(response)
{
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				document.getElementById('PerfReqF').style.visibility = 'hidden';
				mainsplit = response.responseText.split(__MAIN_SEPARATOR__);
				
				if (mainsplit[0] == '')
				{
					document.getElementById('FamiliesError').innerHTML = '';
					var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
					for (var i = 0; i < outputs.length-1; i++)
					{
						var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
						if (outputID.length == 2)
						{
							switch (outputID[0])
							{
								case 'FamilyAdd' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									var fdata = new Array();
									for (var j = 0; j < data.length-1; j+=2) fdata[data[j]] = data[j+1];
									var fid = parseInt(fdata["id"]);
									var fidParent = parseInt(fdata["idParent"]);
									families[fid] = [fdata["name"], fdata["ref_name"], fidParent, fdata["title"], fdata["meta_desc"], fdata["text_content"], 0, []];
									addChild(fidParent, fid);
									SelectFam(fidParent);
									break;
								
								case 'FamilyEdit' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									var fdata = new Array();
									for (var j = 0; j < data.length-1; j+=2) fdata[data[j]] = data[j+1];
									var fid = parseInt(fdata["id"]);
									families[fid][name] = fdata["name"];
									families[fid][ref_name] = fdata["ref_name"];
									families[fid][title] = fdata["title"];
                  families[fid][meta_desc] = fdata["meta_desc"];
                  families[fid][text_content] = fdata["text_content"];
                  
									var fidpo = families[fid][idParent];
									var fidpn = parseInt(fdata["idParent"]);
									if (fidpo != fidpn) {
										delChild(fidpo, fid);
										addChild(fidpn, fid);
										families[fid][idParent] = fidpn;
									}
									SelectFam(fid);
									break;
								
								case 'FamilyDel' :
									var fid = parseInt(outputID[1]);
									var fidParent = families[fid][idParent];
									delete(families[fid]);
									delChild(fidParent, fid);
									SelectFam(fidParent);
									break;
								
								default:
									document.getElementById(outputID[0]).innerHTML = outputID[1];
							}
						}
					}
				}
				else
				{
					var errors = mainsplit[0].split(__ERROR_SEPARATOR__);
					for (var i = 0; i < errors.length-1; i++)
					{
						var errorID = errors[i].split(__ERRORID_SEPARATOR__);
						if (errorID.length == 2)
						{
							document.getElementById(errorID[0]).innerHTML = errorID[1];
						}
					}
				}
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById('PerfReqF').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}
}

function addfam() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	AlterFamilies(
    "&FamilyAction=add"+
    "&FamilyID=0"+
    "&FamilyName="+encodeURIComponent(document.fam.addvalue.value)+
    "&FamilyParentID="+cur_family_id+
    FamilyContent
  );
}

function editName() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editNameValue.value != families[cur_family_id][name])
		AlterFamilies(
      "&FamilyAction=editName"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+families[cur_family_id][idParent]+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function editParent() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editParentValue.value != families[cur_family_id][idParent])
		AlterFamilies(
      "&FamilyAction=editParent"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+document.fam.editParentValue.value+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function editRefName() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editRefNameValue.value != families[cur_family_id][ref_name])
		AlterFamilies(
      "&FamilyAction=editRefName"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+families[cur_family_id][idParent]+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function editTitle() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editTitleValue.value != families[cur_family_id][title])
		AlterFamilies(
      "&FamilyAction=editTitle"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+families[cur_family_id][idParent]+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function editDesc() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editDescValue.value != families[cur_family_id][meta_desc])
		AlterFamilies(
      "&FamilyAction=editDesc"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+families[cur_family_id][idParent]+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function editContent() {
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	if (document.fam.editContentValue.value != families[cur_family_id][text_content])
		AlterFamilies(
      "&FamilyAction=editContent"+
      "&FamilyID="+cur_family_id+
      "&FamilyName="+encodeURIComponent(document.fam.editNameValue.value)+
      "&FamilyParentID="+families[cur_family_id][idParent]+
      "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
      "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
      "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
      FamilyContent
    );
}

function delfam() {
if(confirm("Attention ! La suppression de cette famille sera définitive. Les produits associés ne seront plus disponibles.")){	
  var FamilyContent = typeof document.fam.editContentValue != 'undefined' ? "&FamilyContent="+encodeURIComponent(document.fam.editContentValue.value) : '';
	AlterFamilies(
    "&FamilyAction=delete"+
    "&FamilyID="+cur_family_id+
    "&FamilyName="+encodeURIComponent(families[cur_family_id][name])+
    "&FamilyParentID="+families[cur_family_id][idParent]+
    "&FamilyRefName="+encodeURIComponent(document.fam.editRefNameValue.value)+
    "&FamilyTitle="+encodeURIComponent(document.fam.editTitleValue.value)+
    "&FamilyDesc="+encodeURIComponent(document.fam.editDescValue.value)+
    FamilyContent
  );
}
}
