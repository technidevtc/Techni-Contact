if (!window.HN) HN = window.HN = {};
if (!HN.TC) HN.TC = {};

HN.TC.URLinfos = new HN.URLinfos();

// Constants
HN.TC.Locals = {};

HN.TC.Locals.URL = "http://test.techni-contact.com/";
HN.TC.Locals.SECURE_URL = "https://secure-test.techni-contact.com/fr/";
HN.TC.Locals.AccountURL = HN.TC.Locals.SECURE_URL+"compte/";
HN.TC.Locals.OrderURL = HN.TC.Locals.SECURE_URL+"commande/";
HN.TC.Locals.RessourcesURL = (HN.TC.URLinfos.protocol == "http" ? HN.TC.Locals.URL : HN.TC.Locals.SECURE_URL) + "ressources/";
HN.TC.Locals.Be2BillURL = "https://secure-test.be2bill.com/front/form/process";

HN.TC.Locals.AJAX_Cat3ProductsView = HN.TC.Locals.URL + "product-list.php";

HN.TC.Locals.AJAXCartManager = HN.TC.Locals.RessourcesURL + "ajax/AJAXCartManager.php";
HN.TC.Locals.AJAXSendColleagues = HN.TC.Locals.RessourcesURL + "ajax/AJAXSendColleagues.php";
HN.TC.Locals.AJAXGetProductsInfos = HN.TC.Locals.RessourcesURL + "ajax/AJAXGetProductsInfos.php";
HN.TC.Locals.AJAXProductsListManager = HN.TC.Locals.RessourcesURL + "ajax/AJAXProductsListManager.php";
HN.TC.Locals.AJAXNuukik = HN.TC.Locals.RessourcesURL + "ajax/AJAXNuukik.php";

HN.TC.Locals.MM_Width = 968;
HN.TC.Locals.MM_colWidth = 300;
HN.TC.Locals.MM_zonePicWidth = HN.TC.Locals.MM_Width - 800;
HN.TC.Locals.MM_cat2PerCol = 25;
HN.TC.Locals.MM_cat2nbcols = 2;
HN.TC.Locals.MM_subMenuOffsetWidth = 14;
HN.TC.Locals.MM_colsThresh = [{ thresh: 0, nbcols: 3 }, { thresh: 6, nbcols: 4 }, { thresh: 12, nbcols: 5 }];
HN.TC.Locals.PRODUCTS_IMAGE_INC = HN.TC.Locals.RessourcesURL+ "images/produits/";


HN.TC.Locals.MAX_STAR_RATE = 10;
HN.TC.Locals.NB_STARS = HN.TC.Locals.MAX_STAR_RATE/2;
