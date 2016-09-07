/*****************************************************************************/
/******                      Javascript Modules                         ******/
/*****************************************************************************/


function trim(s)
{
	return s.replace(/(^\s*)|(\s*$)/g, '');
}     

// Purge all functions in children objects to avoid IE memory leaks (http://javascript.crockford.com/memory/leak.html)
function purge(d) {
	var a = d.attributes, i, l, n;
	if (a) {
		l = a.length;
		for (i = 0; i < l; i += 1) {
			n = a[i].name;
			if (typeof d[n] === 'function') d[n] = null;
		}
	}
	a = d.childNodes;
	if (a) {
		var l = a.length;
		for (i = 0; i < l; i += 1) purge(d.childNodes[i]);
	}
}

var HN = HN || {};
HN.GVars = HN.GVars || {};
HN.Mods = HN.Mods || {};
HN.Classes = HN.Classes || {};

/****************************************/
/** Families Class **/
/****************************************/
HN.Classes.Families = 

/****************************************/
/** Multiple Products Selection Module **/
/****************************************/
HN.Mods.MPSM = function () {
		if (!HN.Classes.Families)
			HN.Classes.Load("Families");
}



HN.FamiliesBrowser = function () {
	var that = this;
	var family = {id : 0, name : "", ref_name : ""};
	var id = "";
	var built = false;
	var win = null, bg = null, menu = null, colg = null, titre = null, sf = null, colc = null, desc = null, ssf = null;
	
	this.setID = function(_id) { id = _id; }
	this.getCurFamID = function() { return family.id; }
	this.getCurFam = function() { return family; }
	
	this.Build = function () {
		if (win = document.getElementById(id))
		{
			win.className = "family-window";
			if (bg) {
				purge(bg);
				for (var node = bg.childNodes.length-1; node >= 0; node--) bg.removeChild(bg.childNodes[node]);
			}
			else {
				bg = document.createElement('div'); bg.className = "family-window-bg";
			}
				menu = document.createElement('div'); menu.className = "menu";
				cols = document.createElement('div'); cols.className = "cols";
					colg = document.createElement('div'); colg.className = "colg";
						titre = document.createElement('div'); titre.className = "titre";
						sf = document.createElement('div'); sf.className = "sf";
					colc = document.createElement('div'); colc.className = "colc";
						desc = document.createElement('h1'); desc.className = "desc";
						ssf = document.createElement('div'); ssf.className = "ssf";
			
			win.appendChild(bg);
				bg.appendChild(menu);
				bg.appendChild(cols);
					cols.appendChild(colg);
						colg.appendChild(titre);
							titre.appendChild(document.createTextNode("Familles"));
						colg.appendChild(sf);
					cols.appendChild(colc);
						colc.appendChild(desc);
							desc.appendChild(document.createTextNode("Choisissez une famille"));
						colc.appendChild(ssf);
			
			
			menu.current_f = null;
			menu.childrenList = [];
			for (var i = 0; i < families[0][nbchildren]; i++)
			{
				var a = document.createElement("a");
				a.href = "#";
				a.family_id = families[0][children][i];
				a.appendChild(document.createTextNode(families[a.family_id][name]));
				menu.childrenList[a.family_id] = a;
				menu.appendChild(a);
				menu.appendChild(document.createTextNode(" "));

				a.Select = function () { // lors de la sélection
					if (this.parentNode.current_f && this.parentNode.current_f != this) this.parentNode.current_f.UnSelect();
					this.parentNode.current_f = this;
					this.className = 'current';
					
					titre.innerHTML = families[this.family_id][name];
					families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
					
					purge(sf);
					purge(ssf);
					for (var node = sf.childNodes.length-1; node >= 0; node--) sf.removeChild(sf.childNodes[node]);
					for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
					sf.current_sf = null;
					ssf.current_ssf = null;
					sf.childrenList = [];
					ssf.childrenList = [];
					
					for (var j = 0; j < families[this.family_id][nbchildren]; j++)
					{
						var a2 = document.createElement("a");
						a2.href = "#";
						a2.family_id = families[this.family_id][children][j];
						a2.appendChild(document.createTextNode(families[a2.family_id][name]));
						sf.childrenList[a2.family_id] = a2;
						sf.appendChild(a2);
						
						a2.Select = function () {
							if (this.parentNode.current_sf && this.parentNode.current_sf != this) this.parentNode.current_sf.UnSelect();
							this.parentNode.current_sf = this;
							this.className = 'currentUnfolded';
							
							families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
							
							purge(ssf);
							for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
							ssf.current_ssf = null;
							ssf.childrenList = [];
							for (var k = 0; k < families[this.family_id][nbchildren]; k++)
							{
								var a3 = document.createElement("a");
								a3.href = "#";
								a3.family_id = families[this.family_id][children][k];
								a3.appendChild(document.createTextNode(families[a3.family_id][name]));
								ssf.childrenList[a3.family_id] = a3;
								ssf.appendChild(a3);
								
								a3.onclick = function () {
									family.id = this.family_id;
									family.name = families[this.family_id][name];
									family.ref_name = families[this.family_id][ref_name];
									if (this.parentNode.current_ssf && this.parentNode.current_ssf != this) this.parentNode.current_ssf.className = '';
									this.parentNode.current_ssf = this;
									this.className = 'current';
									
									desc.innerHTML = "Famille " + families[this.family_id][name];
									
									return false;
								}
							}
							
							return false;
						}
						
						a2.UnSelect = function () { // fonction pour cacher la ssf courante
							this.className = '';
						}
						
						a2.onclick = a2.Select;
					}
					
					return false;
				}
				
				a.UnSelect = function () { // lors de la déselection
					this.className = '';
				}
				
				a.onclick = a.Select;
				aa = a.cloneNode(true);
				aa.apointer = a;
				aa.onclick = function() { this.apointer.onclick(); return false; }
				sf.appendChild(aa);
			}
			built = true;
		}
	}
	
	this.SelectFamByID = function (fid) {
		var fidr = fid;
		var famTree = [];
		var n = 0;
		while (fidr != 0)
		{
			famTree[n++] = fidr;
			fidr = families[fidr][idParent];
		}
		famTree.reverse();
		for (var i = 0; i < n; i++)
		{
			switch (i)
			{
				case 0: menu.childrenList[famTree[0]].onclick(); break;
				case 1: sf.childrenList[famTree[1]].onclick(); break;
				case 2: ssf.childrenList[famTree[2]].onclick(); break;
				default : break;
			}
		}
	}
	
}
