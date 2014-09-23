var changed_path = false;
$(document).ready(function () {
    $('#setcurrentdate').change(function () {
        var $event_click = $(this);
        if ($event_click.is(':checked')) {
            $('#datefield').attr("disabled", true).val('');
        }
        else {
            $('#datefield').removeAttr("disabled");
        }
    });
    var df = $('#out').val();
    if(df != null && df.length > 0) {
        changed_path = true;
    }
});
function posterDelete(id) {
    $.get(ffcms_host+'/api.php?iface='+loader+'&object=newsposterdelete&id='+id, function(){
        $('#posterobject').remove();
    });
}
function gallerydel(name, id) {
    $.get(ffcms_host+'/api.php?iface='+loader+'&object=jqueryfile&action=delete&name='+name+'&id='+id);
    document.getElementById(name).remove();
}
function pathCallback()
{
    changed_path = true;
}
var keywords1, keywords2 = new Array(), keywords3 = new Array();
function strip_tags(str, allow) {
    // making sure the allow arg is a string containing only tags in lowercase (<a><b><c>)
    allow = (((allow || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');

    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
    var commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return str.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allow.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}
function nonbsp(str) {
    return str.replace(/nbsp/gi, "");
}
function getWords(s) {
    s = nonbsp(strip_tags(s));
    return s.replace(/[^а-яА-Яa-zA-Z]+/g, " ").toLowerCase(); // returns text.. removing of numbers, commas, any spec chars
}
function getKeywords(s) {
    var tmp;
    tmp = getWords(s);
    return tmp.split(" "); // returns Array of words
}
function countKeywords(current_lang) {
    var s = $('#textobject'+current_lang+'.wysi').val();
    if(s.length < 1)
        s = CKEDITOR.instances['textobject'+current_lang].getData();
    var minLengthKeyword = 3;
    var minRepeatKeyword = 3;
    var coincidence = parseFloat(0.7);
    var keywords_count = 7;

    var tmpKeywords1 = getKeywords(s);
    var tmpKeywords2 = new Array();


    //alert(keywords1.length);
    for (i = 0; i < tmpKeywords1.length; i++) {
        var currentWord = tmpKeywords1[i];
        //alert(currentWord.substring(0, 3) );
        if (currentWord.length >= minLengthKeyword) {
            keywords2.push(currentWord); // put the word to the Array keyword2 if the length >= minLengthKeyword
        }
    }


    for (i = 0; i < keywords2.length; i++) {
        var currentWord = keywords2[i];
        currentWordCore = currentWord.substr(0, Math.round(currentWord.length * coincidence));

        //alert(currentWordCore);
        var inwords2 = keywords2.grep(currentWordCore);
        //alert(inwords2);
        if (inwords2.length >= minRepeatKeyword && keywords3.grep(currentWordCore).length < 1) {
            // the word must repeat 3 or more times .. and do not put the word one more time to result array of keywords3
            keywords3.push(currentWord);
        }
    }
    document.getElementById('keywords_'+current_lang).value = keywords3.slice(0, keywords_count);
    keywords2 = new Array();
    keywords3 = new Array();
}
function grep(str) {
    var ar = new Array();
    var arSub = 0;
    for (var i in this) {
        if (typeof this[i] == "string" && this[i].indexOf(str) != -1) {
            ar[arSub] = this[i];
            arSub++;
        }
    }
    return ar;
}
Array.prototype.remove = function (s) {
    for (i = 0; i < this.length; i++) {
        if (s == this[i]) this.splice(i, 1);
    }
}

Array.prototype.grep = grep;


function JSTranslit() {
    this.strTranslit = function (el) {
        new_el = document.getElementById('out');
        A = new Array();
        A["Ё"] = "YO";
        A["Й"] = "I";
        A["Ц"] = "TS";
        A["У"] = "U";
        A["К"] = "K";
        A["Е"] = "E";
        A["Н"] = "N";
        A["Г"] = "G";
        A["Ш"] = "SH";
        A["Щ"] = "SCH";
        A["З"] = "Z";
        A["Х"] = "H";
        A["Ъ"] = "";
        A["ё"] = "yo";
        A["й"] = "i";
        A["ц"] = "ts";
        A["у"] = "u";
        A["к"] = "k";
        A["е"] = "e";
        A["н"] = "n";
        A["г"] = "g";
        A["ш"] = "sh";
        A["щ"] = "sch";
        A["з"] = "z";
        A["х"] = "h";
        A["ъ"] = "";
        A["Ф"] = "F";
        A["Ы"] = "I";
        A["В"] = "V";
        A["А"] = "A";
        A["П"] = "P";
        A["Р"] = "R";
        A["О"] = "O";
        A["Л"] = "L";
        A["Д"] = "D";
        A["Ж"] = "ZH";
        A["Э"] = "E";
        A["ф"] = "f";
        A["ы"] = "i";
        A["в"] = "v";
        A["а"] = "a";
        A["п"] = "p";
        A["р"] = "r";
        A["о"] = "o";
        A["л"] = "l";
        A["д"] = "d";
        A["ж"] = "zh";
        A["э"] = "e";
        A["Я"] = "YA";
        A["Ч"] = "CH";
        A["С"] = "S";
        A["М"] = "M";
        A["И"] = "I";
        A["Т"] = "T";
        A["Ь"] = "";
        A["Б"] = "B";
        A["Ю"] = "YU";
        A["я"] = "ya";
        A["ч"] = "ch";
        A["с"] = "s";
        A["м"] = "m";
        A["и"] = "i";
        A["т"] = "t";
        A["ь"] = "";
        A["б"] = "b";
        A["ю"] = "yu";
        A[" "] = "-";
        if (!changed_path) {
            new_el.value = el.value.replace(/[^A-Za-z0-9\u0410-\u0451_ ]/g, '').replace(/([\u0410-\u0451 ])/g,
                function (str, p1, offset, s) {
                    if (A[str] != 'undefined') {
                        return A[str].toLowerCase();
                    }
                }
            ).replace(/[A-Z]/g,
                function (data) {
                    return data.toLowerCase();
                }
            );
        }
    }
    /* Normalizes a string, eю => eyu */
    this.strNormalize = function (el) {
        if (!el) {
            return;
        }
        this.strTranslit(el);
    }
}
var oJS = new JSTranslit();