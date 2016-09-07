/*****************************************************************************/
/******                      Javascript Modules                         ******/
/*****************************************************************************/


if (!window.HN) HN = window.HN = {};
if (!HN.GVars) HN.GVars = {};
if (!HN.Mods) HN.Mods = {};
if (!HN.Classes) HN.Classes = {};

HN.prefix = "../AJAXressources/";

HN.Classes = {
	prefix : HN.prefix + "classes/"
};
HN.Classes.prototype = HN;

HN.Mods = {
	prefix : HN.prefix + "modules/"
};
HN.Mods.prototype = HN;

HN.GVars = {
	prefix : HN.prefix + "gvars/"
};
HN.GVars.prototype = HN;

/****************************************/
/** Families Class **/
/****************************************/
/*
 * Return Family Objects by indexes using javascript pre-generated files
 * Data from those JS are arrays to save some speed and bandwidth
 * Initialize the Families Global Variable which can be eventually used by other classes/modules
 */
HN.Classes.Families = function () {
	var ClassesPrefix = HN.Classes.prefix + "Families.";
	var GVarsPrefix = HN.GVars.prefix + "Families.";
	
	var AJAXhandle = {
		async: false,
		dataType: "json",
		error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Families Data")},
		timeout: 10000,
		type: "GET"
	};
	
	if (!HN.GVars.Families) HN.GVars.Families = {};
	if (!HN.GVars.Families.Data) {
		AJAXhandle.url = GVarsPrefix + "Data.js";
		AJAXhandle.success = function (data, textStatus) { HN.GVars.Families.Data = data; };
		$.ajax(AJAXhandle);
	}
	
/* Make Family Objects from Data : Transform an Array of Family Data into an Array of Family Objects
 * input: [[0(ID), 1(Name), 2(Ref Name), 3(Parent ID)], [...], ...]
 * output: [{ "id": ID, "name": Name, "refname": RefName, "parentid": ParentID }, {...}, ...] */
	function MFOD(AoFD) {
		var AoFO = [];
		for (var k in AoFD)
		{
			var FO = {};
			FO.id = AoFD[k][0];
			FO.name = AoFD[k][1];
			FO.refname = AoFD[k][2];
			FO.parentid = AoFD[k][3];
			AoFO[k] = FO;
		}
		return AoFO;
	}
	
	this.GetFamilyByID = function (id) {
		if (!HN.GVars.Families.iID) {
			AJAXhandle.url = GVarsPrefix + "iID.js";
			AJAXhandle.success = function (data, textStatus) { HN.GVars.Families.iID = data; };
			$.ajax(AJAXhandle);
		}
		return MFOD([HN.GVars.Families.Data[HN.GVars.Families.iID[id]]])[0];
	}
	
	this.GetFamilyByName = function (name) {
		if (!HN.GVars.Families.iName) {
			AJAXhandle.url = GVarsPrefix + "iName.js";
			AJAXhandle.success = function (data, textStatus) { HN.GVars.Families.iName = data; };
			$.ajax(AJAXhandle);
		}
		return MFOD([HN.GVars.Families.Data[HN.GVars.Families.iName[name]]])[0];
	}
	
	this.GetFamilyByRefName = function (refname) {
		if (!HN.GVars.Families.iRefName) {
			AJAXhandle.url = GVarsPrefix + "iRefName.js";
			AJAXhandle.success = function (data, textStatus) { HN.GVars.Families.iRefName = data; };
			$.ajax(AJAXhandle);
		}
		return MFOD([HN.GVars.Families.Data[HN.GVars.Families.iRefName[refname]]])[0];
	}
	
	this.GetFamilyChildren = function (famObj) {
		if (!HN.GVars.Families.iParentID) {
			AJAXhandle.url = GVarsPrefix + "iParentID.js";
			AJAXhandle.success = function (data, textStatus) { HN.GVars.Families.iParentID = data; };
			$.ajax(AJAXhandle);
		}
		var families = [];
		for (var k in HN.GVars.Families.iParentID[famObj.id]) families[k] = HN.GVars.Families.Data[HN.GVars.Families.iParentID[famObj.id][k]];
		return MFOD(families);
	}

}
HN.Classes.Families.prototype = HN.Classes;


/****************************************/
/** Products Class **/
/****************************************/
/*
 * Return Products Objects given an ID, a family, a advertiser, or a combination of those
 * product_object: {
 * id, idAdvertiser, idTC, timestamp, cg,
 * ci, cc, refSupplier, price, price2,
 * unite, marge, idTVA, contrainteProduit, tauxRemise,
 * similar_items, name, fastdesc, ref_name, alias,
 * keywords, descc, descd, delai_livraison, active }
 */
HN.Classes.Products = function () {
	var ClassesPrefix = HN.Classes.prefix + "Products.";
	
	var AJAXhandle = {
		async: true,
		dataType: "json",
		error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data")},
		timeout: 10000,
		type: "GET",
		url: ClassesPrefix + "php"
	};
	
/* GetProductsByXXX : Return an array of product objects
 * input :
 *  ids = array of product ID's,
 *  callback_function = function call back by the httprequest,
 *  info_lvl = level of information to get (fields number),
 *  sort = sort order of the products,
 *  sortway = desc or asc
 * output : array of product objects
 */
	
	/* By Products ID */
	this.GetProductsByPdtID = function (ids, callback_function) {
		var sids = ids.join(",");
		AJAXhandle.data = "action=get&filter=pdtID&ids="+sids;
		AJAXhandle.data += (arguments[2]) ? "&info_lvl="+arguments[2] : "";
		AJAXhandle.data += (arguments[3]) ? "&sort="+arguments[3] : "";
		AJAXhandle.data += (arguments[4]) ? "&sortway="+arguments[4] : "";
		AJAXhandle.success = function (data, textStatus) {
			callback_function(data);
		}
		$.ajax(AJAXhandle);
	}
	
	/* By Families ID */
	this.GetProductsByFamID = function (ids, callback_function) {
		var sids = ids.join(",");
		AJAXhandle.data = "action=get&filter=famID&ids="+sids;
		AJAXhandle.data += (arguments[2]) ? "&info_lvl="+arguments[2] : "";
		AJAXhandle.data += (arguments[3]) ? "&sort="+arguments[3] : "";
		AJAXhandle.data += (arguments[4]) ? "&sortway="+arguments[4] : "";
		AJAXhandle.success = function (data, textStatus) {
			callback_function(data);
		}
		$.ajax(AJAXhandle);
	}
	
	/* By Advertisers ID */
	this.GetProductsByAdvID = function (ids, callback_function) {
		var sids = ids.join(",");
		AJAXhandle.data = "action=get&filter=advID&ids="+sids;
		AJAXhandle.data += (arguments[2]) ? "&info_lvl="+arguments[2] : "";
		AJAXhandle.data += (arguments[3]) ? "&sort="+arguments[3] : "";
		AJAXhandle.data += (arguments[4]) ? "&sortway="+arguments[4] : "";
		AJAXhandle.success = function (data, textStatus) {
			callback_function(data);
		}
		$.ajax(AJAXhandle);
	}
}
