<script language="javascript" type="text/javascript">
var keywords1, keywords2 = new Array(), keywords3 =  new Array();
function strip_tags(str, allow) {
	  // making sure the allow arg is a string containing only tags in lowercase (<a><b><c>)
	  allow = (((allow || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');

	  var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
	  var commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	  return str.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
	    return allow.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	  });
}
function getWords(s) {
	s = strip_tags(s);
	return s.replace(/[^а-яА-Яa-zA-Z]+/g, " ").toLowerCase(); // returns text.. removing of numbers, commas, any spec chars
}
function getKeywords(s) {
	var tmp;
	tmp = getWords(s);
	return tmp.split(" "); // returns Array of words
}
function countKeywords () {
	var s = document.getElementById('textobject').value;
	var minLengthKeyword = 3;
	var minRepeatKeyword = 3;
	var coincidence = parseFloat(0.7);
	var keywords_count = 7;
	
	var tmpKeywords1 = getKeywords(s);
	var tmpKeywords2 = new Array();
	
	
	//alert(keywords1.length);
	for (i=0;i<tmpKeywords1.length;i++) {
		var currentWord = tmpKeywords1[i];
		//alert(currentWord.substring(0, 3) );
		if (currentWord.length >= minLengthKeyword) {
			keywords2.push(currentWord); // put the word to the Array keyword2 if the length >= minLengthKeyword
		}
	}


	for (i=0;i<keywords2.length;i++) {
		var currentWord = keywords2[i];
		currentWordCore = currentWord.substr(0,Math.round(currentWord.length*coincidence));
		
		//alert(currentWordCore);
		var inwords2 = keywords2.grep(currentWordCore);
		//alert(inwords2);
		if (inwords2.length >= minRepeatKeyword && keywords3.grep(currentWordCore).length <1) { 
			// the word must repeat 3 or more times .. and do not put the word one more time to result array of keywords3
			keywords3.push(currentWord);
		}
	}
	document.getElementById('keywords').value = keywords3.slice(0, keywords_count);
	keywords2 = new Array();
	keywords3 = new Array();
}
function grep(str) {
	var ar = new Array();
	var arSub = 0;
	for (var i in this) {
		if (typeof this[i] == "string" && this[i].indexOf(str) != -1){
			ar[arSub] = this[i];
			arSub++;
		}
	}
	return ar;
}
Array.prototype.remove=function(s){
  for(i=0;i<this .length;i++){
    if(s==this[i]) this.splice(i, 1);
  }
}

Array.prototype.grep = grep;
</script>
<form action="" method="post">
<div class="row">
<div class="span5">
<h5>{$lang::admin_component_static_edit_page_title}</h5>
<input onkeyup="oJS.strNormalize(this)" type="text" name="title" class="input-block-level" value="{$static_title}" />
<span class="help-block">{$lang::admin_component_static_edit_page_title_desc}</span>
{$notify_message}
</div>
<div class="span4">
<h5>{$lang::admin_component_static_edit_page_pathway}</h5>
<div class="input-prepend input-append">
  <span class="add-on"><a href="{$url}/static/{$static_path}.html" target="_blank"><i class="icon-share-alt"></i></a></span>
  <span class="add-on">{$url}/static/</span>
  <input class="level" type="text" id="out" name="pathway" value="{$static_path}">
  <span class="add-on">.html</span>
</div>
<span class="help-block">{$lang::admin_component_static_edit_page_pathway_desc}</span>
</div>
</div>
<div class="row">
<div class="span9">
<h5>{$lang::admin_component_static_edit_page_textarea_title}</h5>
<textarea name="text" id="textobject" class="input-block-level wysi" rows="20">{$static_text}</textarea>
</div>
</div>
<div class="row">
<div class="span5">
<h5>{$lang::admin_component_static_edit_page_description}</h5>
<input type="text" name="description" class="input-block-level" value="{$static_description}" />
<span class="help-block">{$lang::admin_component_static_edit_page_description_desc}</span>
</div>
<div class="span4">
<h5>{$lang::admin_component_static_edit_page_keywords}</h5>
<input type="text" id="keywords" name="keywords" class="input-block-level" value="{$static_keywords}" /><input class="btn btn-info pull-right" type="button" value="{$lang::admin_component_static_edit_page_keybutton_gen}" onClick="countKeywords()">
<span class="help-block">{$lang::admin_component_static_edit_page_keywords_description}</span>
</div>
</div>
<div class="row">
<div class="span5">
	<label>{$lang::admin_component_static_edit_page_date_text}: <input type="text" name="date" value="{$static_date}"/><span class="help-block">{$lang::admin_component_static_edit_page_date_desc}</span></label>
</div>
<div class="span4">
<input type="submit" name="save" value="{$lang::admin_component_static_edit_page_button_save}" class="btn btn-success btn-large" />
</div>
</div>
</form>
<script>
var default_out_length = document.getElementById('out').value.length;
function JSTranslit()
{
	this.strTranslit = function(el)
	{
		new_el = document.getElementById('out');
		A = new Array();
		A["Ё"]="YO";A["Й"]="I";A["Ц"]="TS";A["У"]="U";A["К"]="K";A["Е"]="E";A["Н"]="N";A["Г"]="G";A["Ш"]="SH";A["Щ"]="SCH";A["З"]="Z";A["Х"]="H";A["Ъ"]="";
		A["ё"]="yo";A["й"]="i";A["ц"]="ts";A["у"]="u";A["к"]="k";A["е"]="e";A["н"]="n";A["г"]="g";A["ш"]="sh";A["щ"]="sch";A["з"]="z";A["х"]="h";A["ъ"]="";
		A["Ф"]="F";A["Ы"]="I";A["В"]="V";A["А"]="A";A["П"]="P";A["Р"]="R";A["О"]="O";A["Л"]="L";A["Д"]="D";A["Ж"]="ZH";A["Э"]="E";
		A["ф"]="f";A["ы"]="i";A["в"]="v";A["а"]="a";A["п"]="p";A["р"]="r";A["о"]="o";A["л"]="l";A["д"]="d";A["ж"]="zh";A["э"]="e";
		A["Я"]="YA";A["Ч"]="CH";A["С"]="S";A["М"]="M";A["И"]="I";A["Т"]="T";A["Ь"]="";A["Б"]="B";A["Ю"]="YU";
		A["я"]="ya";A["ч"]="ch";A["с"]="s";A["м"]="m";A["и"]="i";A["т"]="t";A["ь"]="";A["б"]="b";A["ю"]="yu";A[" "]="_";
		if(default_out_length < 1)
		{
			new_el.value = el.value.replace(/([\u0410-\u0451 ])/g,
				function (str,p1,offset,s) {
					if (A[str] != 'undefined'){return A[str].toLowerCase();}
				}
			);
		}
	}
	/* Normalizes a string, eю => eyu */
	this.strNormalize = function(el)
	{
		if (!el) { return; }
		this.strTranslit(el);
	}
}
var oJS = new JSTranslit();
</script>