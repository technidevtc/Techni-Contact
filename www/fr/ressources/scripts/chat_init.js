var iAdvize = {
    jqloaded: false,
    coreloaded: false,
    attach: 0,
    init_done: 0,
    paused: 0,
    opOffline: 0,
    virtualOP: 0,
    altOps: [],
    phoneDisplayed: 0,
    mousetrack_interval: null,
    curlang: 'fr',
    chaturl: 'http://livechat.iadvize.com/',
    bosh_url: 'http://www.iadvize.com/http-bind',
    website_id: 328,
    website_url: 'www.techni-contact.com',
    lastPreview: '',
    lastmouseX: 0,
    lastmouseY: 0,
    curmouseX: 0,
    curmouseY: 0,
    lastScrollPos: 0,
    curWidth: 0,
    curHeight: 0,
    lastWidth: 0,
    lastHeight: 0,
    cosaisietm: null,
    Jidz: null,
    vuid: '67edd380fff92454261f691abba4a16d4b9d95c8c74e9',
    visitor: {},
    operator: {}
};
iAdvize.preferences = {
    "site_id": "328",
    "link_order": "1",
    "linkcat_order": "1",
    "link_shortcut": "Ctrl+Shift+l",
    "offer_order": "1",
    "offercat_order": "1",
    "offer_shortcut": "Ctrl+Shift+o",
    "canned_order": "1",
    "cannedcat_order": "1",
    "canned_shortcut": "Ctrl+Shift+r",
    "canned_autocomplete": "0",
    "visitor_list_shortcut": "Ctrl+Shift+v",
    "param_opconnected": "0",
    "param_connectedwidget": "1",
    "param_noopconnected": "1",
    "param_noconnectedwidget": "1",
    "param_noopavailable": "0",
    "param_noopavailablecontact": "0",
    "param_noavailwidget": "0",
    "param_contacturl": "http:\/\/www.techni-contact.com\/contact.html",
    "param_startmsg": "Bonjour, afin de faciliter le traitement de votre demande, merci d'indiquer votre t\u00e9l\u00e9phone et adresse mail.",
    "param_endmsg": "Je vous remercie pour votre confiance et vous dit \u00e0 tr\u00e8s bient\u00f4t sur www.techni-contact.com",
    "param_startmsg_fr": "Bonjour, afin de faciliter le traitement de votre demande, merci d'indiquer votre t\u00e9l\u00e9phone et adresse mail.",
    "param_endmsg_fr": "Je vous remercie pour votre confiance et vous dit \u00e0 tr\u00e8s bient\u00f4t sur www.techni-contact.com",
    "param_startmsg_en": "",
    "param_endmsg_en": "",
    "param_startmsg_de": "",
    "param_endmsg_de": "",
    "param_satisfaction": "0",
    "param_sound": "message.swf",
    "GAtracking": "1",
    "virtual_agent": "0",
    "virtual_agent_offline": "0"
};
iAdvize.customize = {
    "site_id": "328",
    "font_size": "11",
    "font_family": "Verdana",
    "color_chatbox": "EDEDED",
    "color_chatbar": "f30000",
    "color_but_action": "709810",
    "color_msg_visitor": "F8F8F8",
    "color_msg_operator": "92D7F6",
    "color_msg_alert": "709810",
    "chatbox_position": "2",
    "chatbox_height": "200",
    "chatbox_width": "705",
    "chatboxleft_left": "0",
    "chatbar_height": "47",
    "chatbar_txt1": "Une question ?",
    "chatbar_txt2": "Tapez ici votre question, Techni-Contact vous r\u00e9pond",
    "chatbar_txt1_fr": "Une question ?",
    "chatbar_txt2_fr": "Tapez ici votre question, Techni-Contact vous r\u00e9pond",
    "chatbar_txt1_en": "Question?",
    "chatbar_txt2_en": "Ask your question here. A representative will respond to you.",
    "chatbar_txt1_de": "eine Frage?",
    "chatbar_txt2_de": "Stellen Sie hier Ihre Frage und ein Berater wird Ihnen in Direktschaltung antworten",
    "opacity": "1",
    "avatars": "1",
    "chatbar_avatar": "1",
    "chatbar_avatar_url": "idz_avatar_5.png",
    "sound_alert": "1"
};
iAdvize.proactifData = [{
    "label": "Visite > 7 min",
    "mess": "Bonjour, notre \u00e9quipe est \u00e0 votre disposition pour vous guider, via cette fen\u00eatre de dialogue ou au 01.72.08.01.14\r\n",
    "showDialog": "1",
    "animDialog": "1",
    "id": "299",
    "rules": [{
        "op": "more than",
        "val": "7",
        "type": "TIME",
        "field": "timeElapsed"
    },
    {
        "op": "is",
        "val": "FR",
        "type": "COUNTRY",
        "field": "country"
    }]
},
{
    "label": "Page lead - Engagement apr\u00e8s 2 min pass\u00e9 sur page",
    "mess": "Bonjour, notre \u00e9quipe reste \u00e0 votre disposition pour vous aider \u00e0 remplir votre demande de devis si vous le souhaitez, via cette fen\u00eatre de dialogue ou au 01.72.08.01.14",
    "showDialog": "1",
    "animDialog": "1",
    "id": "293",
    "rules": [{
        "op": "more than",
        "val": "2",
        "type": "TIME",
        "field": "dureePageEnCours"
    },
    {
        "op": "contains",
        "val": "lead.html",
        "type": "STRING",
        "field": "actualURI"
    },
    {
        "op": "is",
        "val": "FR",
        "type": "COUNTRY",
        "field": "country"
    }]
}];
iAdvize.chatbox_state = 4;
iAdvize.chatbar_state = 0;
iAdvize.opDisconnected = 0;
iAdvize.vStats = {
    val: function (key, value) {
        this['ext_' + key] = value;
        return this;
    }
}
iAdvize.vStats['actualURI'] = document.location.href;
iAdvize.vProf = {
    "site_id": "328",
    "chatcount": 0,
    "nbrVisite": 16,
    "country": "FR",
    "country_name": "France",
    "city": "Marseille",
    "lat": 43.2999992371,
    "long": 5.40000009537,
    "lang": "fr",
    "visitorname": " ",
    "pageview": 5,
    "connectionTime": 1281979013,
    "navTime": 2007000,
    "referrer_lastPage": null,
    "timeElapsed": 33.45
};
for (var v in iAdvize.vProf) {
    iAdvize.vStats[v] = iAdvize.vProf[v];
}
iAdvize.trad = {
    "CONNECTION_ERREUR": "Oops ! Une erreur est survenue lors de votre connexion, veuillez rafra\u00eechir la page. ",
    "VISITEUR_CHATBOX_OPEN": "Le visiteur vient d'ouvrir la fen\u00eatre de dialogue !",
    "VISITOR_CHATBOX_MINIMIZE": "Le visiteur vient de r\u00e9duire la fen\u00eatre de dialogue !",
    "VISITEUR_CHATBAR_OPEN": "Le visiteur vient d'ouvrir la barre de dialogue !",
    "VISITOR_CHATBAR_MINIMIZE": "Le visiteur vient de r\u00e9duire la barre de dialogue !",
    "VISITOR_CHATBOX_CLOSE": "Le visiteur vient de fermer la fen\u00eatre de dialogue !",
    "VISITOR_CONFIRM_CLOSE": "En fermant la fen\u00eatre de dialogue vous mettrez fin \u00e0 la discussion et cacherez la fen\u00eatre de dialogue d\u00e9finitivement. Etes-vous sur de vouloir continuer ?",
    "COBROWSING_ACCEPT": "Vous avez accept\u00e9 la proposition d'assistance de navigation.",
    "COBROWSING_REFUSE": "Vous avez refus\u00e9 la proposition d'assistance de navigation.",
    "COBROWSING_STOP": "Je viens de vous rendre le contr\u00f4le de la navigation.",
    "COBROWSING_STOP2": "Fin d'assistance de la navigation.",
    "COBROWSING_ASK": "Je souhaite vous assister dans votre navigation.",
    "QUE_FAIRE": "Que souhaitez-vous faire ?",
    "COBROWSING_ASK_TEXT": "En acceptant d'\u00eatre guid\u00e9 notre repr\u00e9sentant pourra vous faire naviguer uniquement \u00e0 travers notre site. Et vous pourrez continuer de naviguer simultan\u00e9ment.",
    "COBROWSING_ASK_SHORT": "Demande de prise de contr\u00f4le de la navigation",
    "OPERATEUR_DECONNECTION": "Votre conseiller vient d'\u00eatre soudainement d\u00e9connect\u00e9 ! Veuillez-nous en excuser, nous vous prions de bien vouloir patienter le temps qu'il se reconnecte.",
    "OPERATEUR_RECONNECTION": "Votre conseiller vient de se reconnecter. Merci pour votre patience.",
    "OPERATEUR_COMPOSING": "l'op\u00e9rateur est en train d'\u00e9crire...",
    "OPERATEUR_PAUSED": "l'op\u00e9rateur n'\u00e9crit plus.",
    "OPERATEUR_PUSHLINK": "Je viens de vous transf\u00e9rer vers une page.<br \/>Retrouvez le lien vers cette page dans la fen\u00eatre de gauche.",
    "OPERATEUR_SENDLINK": "Je viens de vous envoyer un lien.<br \/>Celui-ci est visible dans la fen\u00eatre de gauche.",
    "OPERATEUR_PUSHOFFER": "Je viens de vous transf\u00e9rer vers une page.<br \/>Retrouvez l'offre relative \u00e0 cette page dans la fen\u00eatre de gauche.",
    "OPERATEUR_SENDOFFER": "Je viens de vous envoyer une offre.<br \/>Celle-ci est visible dans la fen\u00eatre de gauche.",
    "FIN_DISCUSSION": "Notre discussion prendra fin dans 5 secondes. Cette fen\u00eatre va se r\u00e9duire. <br \/>Vous pourrez \u00e0 tout moment retrouver l'historique de notre discussion au cours de votre navigation.",
    "J_ACCEPTE": "J'accepte",
    "JE_REFUSE": "Je refuse",
    "FIN_DISCUSSION_SHORT": "Fin de la discussion.<br \/>Vous pouvez retrouver l'historique de votre discussion \u00e0 tout moment.",
    "PARLER_VIVE_VOIX": "Vous souhaitez parler de vive voix ?",
    "BESOIN_AIDE": "Une question ?",
    "ENVOYER": "Envoyer",
    "JAI_SELECT_POUR_VOUS": "J'ai s\u00e9lectionn\u00e9 pour vous: ",
    "RECEVOIR_INFOS": "Recevez ces informations",
    "PRINT": "imprimer",
    "SEND_EMAIL": "recevoir par email",
    "POSER_VOTRE_QUESTION_ICI_UN_CONSEILLER_VOUS_REPONDRA": "Posez votre question ici, un conseiller vous r\u00e9pondra en direct",
    "POSER_VOTRE_QUESTION_ICI": "Posez votre question ici",
    "VOTRE_COMMENTAIRE_LIBRE": "Votre commentaire libre",
    "POURSUIVRE_CONVERSATTION_INTERFACE_IADVIZE": "Poursuivre cette conversation sur l'interface d'iAdvize",
    "VOTRE_AVIS": "Votre avis",
    "VALIDER": "Valider",
    "LA_REGLE": "La r\u00e8gle",
    "VIENT_ETRE_EXECUTEE": "vient d'\u00eatre ex\u00e9cut\u00e9e",
    "CE_MESSAGE_ENVOYE_VISITEUR": "Ce message a \u00e9t\u00e9 envoy\u00e9 au visiteur",
    "EN_LIGNE": "En ligne",
    "HORS_LIGNE": "Hors ligne",
    "BONJOUR_NOTRE_SERVICE_CLIENT_EST_ACTUELLEMENT_FERME_PAGE_CONTACT": "Bonjour, notre service client par chat est actuellement indisponible. Nous vous invitons \u00e0 laisser un message sur <a href='##URL##' class='strong'>notre page de contact<\/a>. ",
    "BONJOUR_NOTRE_SERVICE_EST_FERME_NOUS_VOUS_INVITONS_LAISSER_UN_MESSAGE_PAGE_CONTACT": "Bonjour, aucun op\u00e9rateur n'est actuellement disponible. Nous vous invitons \u00e0 laisser un message sur <a href='##URL##' class='strong'>notre page de contact<\/a>.",
    "BONJOUR_AUCUN_CONSEILLER_EST_DISPONIBLE_NOUS_VOUS_INVITONS_LAISSER_UN_MESSAGE_PAGE_CONTACT": "Bonjour, aucun conseiller n'est actuellement disponible. Nous vous prions de bien vouloir patienter le temps qu'un conseiller se lib\u00e8re. ",
    "BONJOUR_AUCUN_CONSEILLER_N_EST_ACTUELLEMENT_DISPONIBLE_NOUS_VOUS_PRIONS_PATIENTER": "Bonjour, aucun op\u00e9rateur n'est actuellement disponible. Nous vous prions de bien vouloir patienter le temps qu'un conseiller se lib\u00e8re. ",
    "POUR_VOUS_EVITER_D_ATTENDRE_NOUV_VOUS_INVITONS_LAISSER_MESSAGE_PAR_PAGE_CONTACT": "Pour vous \u00e9viter d'attendre nous vous invitons \u00e0 laisser un message sur <a href='##URL##'>notre page de contact<\/a>. ",
    "HISTORIQUE_DE_DISCUSSION_AVEC_UN_REPRESENTANT_DE": "Historique de discussion avec un repr\u00e9sentant de ",
    "CREE_LE": "cr\u00e9\u00e9 le ",
    "OPERATEUR": "Op\u00e9rateur",
    "VOUS": "Vous",
    "NOUS_AVONS_SELECTIONNE_POUR_VOUS": "Nous avons s\u00e9lectionn\u00e9 pour vous",
    "EMAILING_DISCUSSION_CORRECTEMENT_ENVOYE": "Votre email a bien \u00e9t\u00e9 envoy\u00e9 !",
    "EMAILING_DISCUSSION_ERREUR": "Une erreur s'est produite lors de l'envoie de votre email !",
    "INVALID_EMAIL": "Veuillez fournir un email valide.",
    "HISTORIQUE_NOTRE_DISCUSSION": "Historique de notre discussion",
    "DU": "du",
    "RETROUVEZ_NOUS_SUR": "Retrouvez nous sur ",
    "NOTRE_DISCUSSION": "Notre discussion",
    "SELECTIONNE_POUR_VOUS": "J'ai s\u00e9l\u00e9ctionn\u00e9 pour vous",
    "MENTIONS_LEGALES_MAILING_HISTORIQUE_CONVERSATION": "Cet email a \u00e9t\u00e9 adress\u00e9 \u00e0 votre demande suite \u00e0 votre discussion sur le site ",
    "MSG_ENQUETE_SATISFACTION": "Votre satisfaction est au coeur de nos pr\u00e9occupations. Et nous vous serions reconnaissant si vous preniez quelques secondes pour \u00e9valuer la qualit\u00e9 de notre support client.",
    "SATISFACTION_QUESTION1": "Accueil de notre conseiller",
    "SATISFACTION_QUESTION2": "Votre d\u00e9lai d'attente",
    "SATISFACTION_QUESTION3": "Qualit\u00e9 de la r\u00e9ponse",
    "MSG_VALIDER_ENQUETE": "Apr\u00e8s avoir r\u00e9pondu aux questions, cliquez sur \"Valider\".  Vous pouvez \u00e9galement laisser un commentaire ci-dessous.",
    "SATISFACTION_REPONDU": "Le visiteur a r\u00e9pondu au questionnaire de satisfaction",
    "EMAIL_PROMPT": "Indiquez votre adresse e-mail pour recevoir le r\u00e9capitulatif de notre discussion",
    "AUCUNE_INFO_DISPO": "Aucune infos disponible.",
    "OPERATEUR_TRANSFER": "Je viens de vous transf\u00e9rer vers l'un de nos repr\u00e9sentants",
    "OPERATEUR_TRANSFER_CONV": "vient de vous transf\u00e9rer cette conversation",
    "SAISIR_UN_MESSAGE": "Veuillez saisir votre message instantan\u00e9 ci-dessous, l'un de nos conseillers vous r\u00e9pondra en direct dans les plus brefs d\u00e9lais.",
    "VISITOR_DEJA_CONVERSATION_AVEC": "Ce visiteur est d\u00e9ja en conversation avec"
};
iAdvize.T = function (s) {
    return iAdvize.trad[s] || s;
}
iAdvize.util = {
    detectDoctype: function () {
        var re = /\s+(X?HTML)\s+([\d\.]+)\s*([^\/]+)*\//gi;
        var loose = 0;
        if (typeof document.namespaces != "undefined") {
            if (document.all[0].nodeType == 8) {
                re.exec(document.all[0].nodeValue);
                if (document.all[0].nodeValue.indexOf('loose') != -1) {
                    loose = 1;
                }
            }
            else {
                return null;
            }
        } else {
            if (document.doctype != null) {
                re.exec(document.doctype.publicId);
                if (document.doctype.systemId.indexOf('loose') != -1) {
                    loose = 1;
                }
            }
            else {
                return null;
            }
        }
        var dtype = {
            'xhtml': RegExp.$1,
            'version': RegExp.$2,
            'importance': RegExp.$3,
            'loose': loose
        };
        return dtype;
    },
    get_body: function () {
        var tmp = document.getElementsByTagName("html");
        var html = null;
        if (tmp.length < 1) {
            html = document.createElement("html");
            document.appendChild(html);
        } else {
            html = tmp[0];
        }
        tmp = document.getElementsByTagName('body');
        var docbody = null;
        if (tmp.length > 0) {
            docbody = document.getElementsByTagName('body').item(0);
        } else {
            docbody = document.createElement('body');
            html.appendChild(docbody);
        }
        return docbody;
    },
    get_head: function () {
        var tmp = document.getElementsByTagName("html");
        var html = null;
        if (tmp.length < 1) {
            html = document.createElement("html");
            document.appendChild(html);
        } else {
            html = tmp[0];
        }
        tmp = document.getElementsByTagName('head');
        var dochead = null;
        if (tmp.length > 0) {
            dochead = document.getElementsByTagName('head').item(0);
        } else {
            dochead = document.createElement('head');
            html.appendChild(dochead);
        }
        return dochead;
    },
    load_js: function (url, callback) {
        var dochead = this.get_head();
        var js = document.createElement('script');
        js.setAttribute('language', 'javascript');
        js.setAttribute('type', 'text/javascript');
        js.setAttribute('src', url);
        dochead.appendChild(js);
        var loaded = false;
        js.onload = callback;
        js.onreadystatechange = function () {
            if (this.readyState == 'complete' || this.readyState == 'loaded') {
                if (loaded) {
                    return;
                }
                callback();
            }
        };
    },
    load_css: function (filename) {
        var dochead = this.get_head();
        var newcss = document.createElement('link');
        newcss.setAttribute("rel", "stylesheet");
        newcss.setAttribute("type", "text/css");
        newcss.setAttribute("href", filename);
        dochead.appendChild(newcss);
    },
    addScript: function (id, src) {
        var dt = new Date();
        var old = document.getElementById(id);
        if (old !== null) {
            old.parentNode.removeChild(old);
        }
        var head = this.get_head();
        var script = document.createElement('script');
        script.id = id;
        script.type = 'text/javascript';
        script.src = src + '&random=' + dt.getTime();
        head.appendChild(script);
    },
    delScript: function (id) {
        var old = document.getElementById(id);
        if (old !== null) {
            old.parentNode.removeChild(old);
        }
    },
    addEvent: function (obj, evType, fn, useCapture) {
        if (obj.addEventListener) {
            obj.addEventListener(evType, fn, useCapture);
            return true;
        } else if (obj.attachEvent) {
            var r = obj.attachEvent("on" + evType, fn);
            return r;
        } else {
            alert("Handler could not be attached");
        }
    },
    removeEvent: function (obj, evType, fn, useCapture) {
        if (obj.removeEventListener) {
            obj.removeEventListener(evType, fn, useCapture);
            return true;
        } else if (obj.detachEvent) {
            var r = obj.detachEvent("on" + evType, fn);
            return r;
        } else {
            alert("Handler could not be removed");
        }
    },
    setCookie: function (name, value) {
        var expire = new Date();
        expire.setTime(expire.getTime() + (60 * 1000));
        document.cookie = name + "=" + value + "; expires=" + expire.toGMTString();
    },
    getCookie: function (name) {
        var start = document.cookie.indexOf(name + "=");
        var len = start + name.length + 1;
        if ((!start) && (name != document.cookie.substring(0, name.length))) {
            return null;
        }
        if (start == -1) {
            return null;
        }
        var end = document.cookie.indexOf(';', len);
        if (end == -1) {
            end = document.cookie.length;
        }
        return unescape(document.cookie.substring(len, end));
    },
    delCookie: function (name) {
        var expire = new Date();
        expire.setTime(expire.getTime() - (60 * 1000));
        document.cookie = name + "= ; expires=" + expire.toGMTString();
    },
    debug: function (msg) {},
    info: function (msg) {},
    warn: function (msg) {},
    error: function (msg) {},
    profile: function (msg) {},
    nl2br: function (str) {
        var breakTag = '<br/>';
        return (str + '').replace(/([^>]?)\n/g, '$1' + breakTag).replace(/[\n\r\t]/g, '');
    },
    htmlspecialchars: function (str) {
        if (typeof(str) == "string") {
            str = str.replace(/&/g, "&amp;");
            str = str.replace(/"/g, "&quot;");
            str = str.replace(/'/g, "&#039;");
            str = str.replace(/</g, "&lt;");
            str = str.replace(/>/g, "&gt;");
        }
        return str;
    },
    long2ip: function (proper_address) {
        var output = false;
        if (!isNaN(proper_address) && (proper_address >= 0 || proper_address <= 4294967295)) {
            output = Math.floor(proper_address / Math.pow(256, 3)) + '.' + Math.floor((proper_address % Math.pow(256, 3)) / Math.pow(256, 2)) + '.' + Math.floor(((proper_address % Math.pow(256, 3)) % Math.pow(256, 2)) / Math.pow(256, 1)) + '.' + Math.floor((((proper_address % Math.pow(256, 3)) % Math.pow(256, 2)) % Math.pow(256, 1)) / Math.pow(256, 0));
        }
        return output;
    }
};
iAdvize.XMPPconnect = function (data) {
    iAdvize.util.profile('XMPP CONNECTION');
    iAdvize.util.debug('EVENT: XMPP connect');
    iAdvize.vjid = data.jid;
    iAdvize.vsid = data.sid;
    iAdvize.vrid = data.rid;
    iAdvize.connection = new Strophe.Connection(iAdvize.bosh_url);
    iAdvize.connection.rawInput = function (data) {};
    iAdvize.connection.rawOutput = function (data) {};
    iAdvize.connection.attach(data.jid, data.sid, data.rid, function (status) {
        if (status == Strophe.Status.CONNECTING) {
            iAdvize.util.debug('Strophe is connecting.');
        } else if (status == Strophe.Status.CONNFAIL) {
            iAdvize.util.error('Strophe failed to connect.');
        } else if (status == Strophe.Status.DISCONNECTED) {
            iAdvize.util.error('Strophe is disconnected.');
        } else if (status == Strophe.Status.DISCONNECTING) {
            iAdvize.util.error('Strophe is disconnecting.');
            iAdvize.XMPPdisconnected();
        } else if (status == Strophe.Status.CONNECTED || status == 8) {
            iAdvize.XMPPconnected();
        }
    }, 60, 2, 3);
};
iAdvize.XMPPdisconnected = function () {
    iAdvize.chat.connected = false;
    if (iAdvize.chat.reconnecting === true) {
        return;
    }
    iAdvize.chat.reconnecting = true;
    if (iAdvize.opOffline != 1 && iAdvize.paused != 1) {
        iAdvize.util.info('trying to reconnect.');
        iAdvize.connection.reset();
        if (iAdvize.BrowserDetect.browser == 'Explorer' && iAdvize.BrowserDetect.version == 6) {
            setTimeout(function () {
                iAdvize.util.addScript('reconnect', iAdvize.chaturl + 'rpc/reconnect.php?nav=ie6&reconnect=' + iAdvize.website_id + '&vuid=67edd380fff92454261f691abba4a16d4b9d95c8c74e9');
            }, 1000);
        } else {
            setTimeout(function () {
                iAdvize.util.addScript('reconnect', iAdvize.chaturl + 'rpc/reconnect.php?reconnect=' + iAdvize.website_id + '&vuid=67edd380fff92454261f691abba4a16d4b9d95c8c74e9');
            }, 1000);
        }
    }
};
iAdvize.XMPPconnected = function () {
    iAdvize.util.info('EVENT: XMPP connected');
    iAdvize.chat.connected = true;
    iAdvize.bindUnloadEvent();
    iAdvize.chat.cCook = document.cookie;
    iAdvize.util.profile('BUILD CHAT');
    iAdvize.chat.buildChat();
    iAdvize.util.profile('BUILD CHAT');
    iAdvize.connection.addHandler(iAdvize.chat.onRosterChanged, Strophe.NS.ROSTER, "iq", "set");
    iAdvize.connection.addHandler(iAdvize.chat.onPresence, null, "presence");
    iAdvize.connection.addHandler(iAdvize.chat.onMessage, null, 'message');
    iAdvize.connection.addHandler(iAdvize.chat.onDiscoInfo, Strophe.NS.DISCO_INFO, "iq", "get");
    if (iAdvize.attach != 1) {
        iAdvize.util.debug('ask for roster.');
        var roster_iq = $iq({
            type: "get"
        }).c('query', {
            xmlns: Strophe.NS.ROSTER
        });
        iAdvize.connection.sendIQ(roster_iq, function (iq) {
            Jidz(iq).find("item").each(function () {
                var contact = new iAdvize.chat.Contact();
                contact.name = Jidz(this).attr('name') || "";
                contact.subscription = Jidz(this).attr('subscription') || "none";
                contact.ask = Jidz(this).attr('ask') || "";
                Jidz(this).find("group").each(function () {
                    contact.groups.push(Jidz(this).text());
                });
                iAdvize.vRoster.contacts[Jidz(this).attr('jid')] = contact;
            });
            iAdvize.util.debug('Roster received.');
            iAdvize.JSONStore.saveData({
                vRoster: iAdvize.vRoster.contacts
            });
            iAdvize.doInit();
        });
    } else {
        iAdvize.vRoster.contacts = null;
        iAdvize.doInit();
    }
};
iAdvize.findAvailableOP = function () {
    setTimeout(function () {
        iAdvize.util.addScript('findop', iAdvize.chaturl + 'rpc/findop.php?findop=' + iAdvize.website_id);
    }, 1300);
}
iAdvize.bindUnloadEvent = function () {
    if (iAdvize.BrowserDetect.browser == 'Chrome' || iAdvize.BrowserDetect.browser == 'Safari') {
        iAdvize.util.addEvent(window, "unload", iAdvize.unloadXHR, false);
        iAdvize.util.addEvent(window, "beforeunload", iAdvize.beforeunloadXHR, false);
    } else if (iAdvize.BrowserDetect.browser == 'Opera') {
        iAdvize.util.addEvent(window, "unload", function () {
            iAdvize.connection.pause();
            iAdvize.paused = 1;
            iAdvize.util.addScript('away', iAdvize.chaturl + 'rpc/away.php?away=' + iAdvize.website_id + '&s=' + iAdvize.vsid + '&r=' + iAdvize.connection.rid + '&j=' + iAdvize.vjid + '&u=' + iAdvize.vStats['vuid'] + '&' + Math.random());
            iAdvize.sleeeep(500);
        }, false);
    } else if (iAdvize.BrowserDetect.browser == 'Explorer' && iAdvize.BrowserDetect.version == 8) {
        iAdvize.util.addEvent(window, "unload", iAdvize.unloadFuncIE8, false);
        iAdvize.util.addEvent(window, "beforeunload", iAdvize.beforeunloadFuncIE8, false);
    } else {
        iAdvize.util.addEvent(window, "unload", iAdvize.unloadFunc, false);
        iAdvize.util.addEvent(window, "beforeunload", iAdvize.beforeunloadFunc, false);
    }
}
iAdvize.onCoreLoaded = function () {
    if (!iAdvize.coreloaded) {
        iAdvize.coreloaded = true;
        iAdvize.BrowserDetect.init();
        if (iAdvize.BrowserDetect.browser == 'Explorer' && iAdvize.BrowserDetect.version == 6) {
            return;
        }
        iAdvize.vStats['winWidth'] = Jidz(window).width();
        iAdvize.vStats['winHeight'] = Jidz(window).height();
        iAdvize.curWidth = iAdvize.lastWidth = Jidz(window).width();
        iAdvize.curHeight = iAdvize.lastHeight = Jidz(window).height();
        iAdvize.vStats['vuid'] = "67edd380fff92454261f691abba4a16d4b9d95c8c74e9";
        iAdvize.vStats['ip'] = "1415643656";
        iAdvize.vStats['browserInfo'] = iAdvize.BrowserDetect.browser + ' ' + iAdvize.BrowserDetect.version + ' on ' + iAdvize.BrowserDetect.OS;
        if (typeof(window['idzCustomData']) != "undefined") {
            for (var c in idzCustomData) {
                if (idzCustomData[c] !== null) {
                    iAdvize.vStats.val(c, idzCustomData[c]);
                }
            }
        }
        iAdvize.chat.history_id = 0;
        iAdvize.vStats['history_id'] = iAdvize.chat.history_id;
        iAdvize.chat.status = 1;
        iAdvize.chat.satisfaction = 0;
        iAdvize.chat.cobrowsing = 0;
        iAdvize.chat.msgQ = null;
        iAdvize.proactif_timeElapsed = 1281979013;
        iAdvize.proactif_activated = 0;
        iAdvize.proactif_showchatbar = 0;
        iAdvize.proactif_showbuttons = 0;
        iAdvize.proactif_message = null;
        iAdvize.proactif_alert = null;
        if (iAdvize.opOffline !== 1) {
            if (iAdvize.BrowserDetect.browser == 'Explorer' && iAdvize.BrowserDetect.version == 6) {
                setTimeout(function () {
                    iAdvize.util.addScript('connect', iAdvize.chaturl + 'rpc/connect.php?nav=ie6&connect=' + iAdvize.website_id + '&vuid=67edd380fff92454261f691abba4a16d4b9d95c8c74e9');
                }, 50);
            } else {
                setTimeout(function () {
                    iAdvize.util.addScript('connect', iAdvize.chaturl + 'rpc/connect.php?connect=' + iAdvize.website_id + '&vuid=67edd380fff92454261f691abba4a16d4b9d95c8c74e9');
                }, 50);
            }
            iAdvize.hasFocus = 1;
            iAdvize.blinktm = null;
            iAdvize.oTitle = document.title;
            Jidz(window).focus(function () {
                iAdvize.hasFocus = 1;
                if (iAdvize.blinktm !== null) {
                    clearInterval(iAdvize.blinktm);
                    iAdvize.blinktm = null;
                    document.title = iAdvize.oTitle;
                }
            });
            Jidz(window).blur(function () {
                iAdvize.hasFocus = 0;
            });
        } else {
            iAdvize.chat.buildChat();
            setTimeout(function () {
                Jidz('#idz_chatglobal').show();
                Jidz('#idz_bottompad').show();
            }, 500);
        }
    }
};
iAdvize.onJqueryLoaded = function () {
    if (!iAdvize.jqloaded) {
        iAdvize.jqloaded = true;
        iAdvize.util.load_css(iAdvize.chaturl + "css/livechat/import.css?v=1850");
        iAdvize.util.load_css(iAdvize.chaturl + "css/livechat/override.css?v=1850&sid=" + iAdvize.website_id);
        Jidz(document).keypress(function (e) {
            if (e.keyCode == 27) {
                e.preventDefault();
            }
        });
        var cors = false;
        if (Jidz.browser.mozilla && Jidz.browser.version >= '1.9.1') {
            cors = true;
        }
        if (Jidz.browser.webkit && parseInt(Jidz.browser.version) >= '528') {
            cors = true;
        }
        if (!cors) {
            iAdvize.bosh_url = iAdvize.chaturl + 'jproxy.php';
            Strophe.addConnectionPlugin('jxhr', {
                init: function (s) {
                    Strophe.Request.prototype._newXHR = function () {
                        var xhr = new jXHR();
                        try {
                            xhr.onreadystatechange = this.func.prependArg(this);
                        } catch (e) {}
                        return xhr;
                    };
                }
            });
        }
        iAdvize.util.load_js(iAdvize.chaturl + "chat_core.js?v=1917", iAdvize.onCoreLoaded);
    }
};
iAdvize.addDOMLoadEvent = (function () {
    var e = [],
        t, s, n, i, o, d = document,
        w = window,
        r = 'readyState',
        c = 'onreadystatechange',
        x = function () {
            n = 1;
            clearInterval(t);
            while (i = e.shift()) i();
            if (s) s[c] = ''
        };
    return function (f) {
        if (n) return f();
        if (!e[0]) {
            d.addEventListener && d.addEventListener("DOMContentLoaded", x, false);
            if (/WebKit/i.test(navigator.userAgent)) t = setInterval(function () {
                /loaded|complete/.test(d[r]) && x()
            }, 10);
            o = w.onload;
            w.onload = function () {
                x();
                o && o()
            }
        }
        e.push(f)
    }
})();
iAdvize.autoAnswer = "Bonjour, notre service client par chat est actuellement indisponible. Nous vous invitons à laisser un message sur <a href='http://www.techni-contact.com/contact.html' class='strong'>notre page de contact</a>. ";
iAdvize.bosh_host = "iadvize.com";
iAdvize.addDOMLoadEvent(function () {
    if (iAdvize.scriptLoaded === true) {
        iAdvize.util.error('IADVIZE SCRIPT ALREADY LOADED');
        return;
    }
    iAdvize.scriptLoaded = true;
    w = window.location.href;
    if (w.indexOf('iadvize.com/pupitre', 0) == -1 && w.indexOf('iadvize.com/admin/?go=customize', 0) == -1) {
        var altFound = 0;
        if (w.indexOf(iAdvize.website_url, 0) != -1 || altFound != 0) {
            iAdvize.opOffline = 1;

            function goToContactPage() {
                window.location = 'http://www.techni-contact.com/contact.html';
            }
            if (document.getElementById('button_offline') != null) {
                iAdvize.util.addEvent(document.getElementById('button_offline'), "click", goToContactPage, false);
            }
            setTimeout(function () {
                iAdvize.findAvailableOP();
            }, 60000);
        }
    }

});