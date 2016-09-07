var iMaxFilesize = 524288; // 0.5MB
var oTimer = 0;
var sResultFileSize = '';

function fileSelected(photo_type) {
   if(photo_type=='photo_facade'){
    // get selected file element
    var oFile = document.getElementById('photo_facade').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade'){
       // get preview element
        var oImage = document.getElementById('preview_facade');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit0(photo_type) {
   if(photo_type=='adress_picture_produit0'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit0').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit0'){
        var oImage = document.getElementById('preview_facade_produit0');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected_produit1(photo_type) {		
   if(photo_type=='adress_picture_produit1'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit1').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit1'){
        var oImage = document.getElementById('preview_facade_produit1');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit2(photo_type) {	
   if(photo_type=='adress_picture_produit2'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit2').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit2'){
        var oImage = document.getElementById('preview_facade_produit2');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit3(photo_type) {	
   if(photo_type=='adress_picture_produit3'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit3').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit3'){
        var oImage = document.getElementById('preview_facade_produit3');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit4(photo_type) {	
   if(photo_type=='adress_picture_produit4'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit4').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit4'){
        var oImage = document.getElementById('preview_facade_produit4');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit5(photo_type) {	
   if(photo_type=='adress_picture_produit5'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit5').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit5'){
        var oImage = document.getElementById('preview_facade_produit5');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected_produit6(photo_type) {	
   if(photo_type=='adress_picture_produit6'){
    // get selected file element
    var oFile = document.getElementById('adress_picture_produit6').files[0];	
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='adress_picture_produit6'){
        var oImage = document.getElementById('preview_facade_produit6');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected1(photo_type) {
   if(photo_type=='photo_facade_produit1'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit1').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit1'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit1');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected0(photo_type) {
   if(photo_type=='photo_facade_produit0'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit0').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit0'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit0');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected2(photo_type) {
   if(photo_type=='photo_facade_produit2'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit2').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit2'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit2');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected3(photo_type) {
   if(photo_type=='photo_facade_produit3'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit3').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit3'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit3');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected4(photo_type) {
   if(photo_type=='photo_facade_produit4'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit4').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit4'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit4');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected5(photo_type) {
   if(photo_type=='photo_facade_produit5'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit5').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit5'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit5');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected6(photo_type) {
   if(photo_type=='photo_facade_produit6'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit6').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit6'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit6');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected7(photo_type) {
   if(photo_type=='photo_facade_produit7'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit7').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit7'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit7');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected8(photo_type) {
   if(photo_type=='photo_facade_produit8'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit8').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit8'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit8');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}

function fileSelected9(photo_type) {
   if(photo_type=='photo_facade_produit9'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit9').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit9'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit9');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


function fileSelected10(photo_type) {
   if(photo_type=='photo_facade_produit10'){
    // get selected file element
    var oFile = document.getElementById('photo_facade_produit10').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade_produit10'){
       // get preview element
        var oImage = document.getElementById('preview_facade_produit10');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}