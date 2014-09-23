function translateText(lang_source, lang_target, source_text, api_key, blockname, ckeditor_instance) {
    $.get('https://translate.yandex.net/api/v1.5/tr.json/translate?key='+api_key+'&text='+source_text+'&lang='+lang_source+'-'+lang_target+'&format=html', function(result) {
        if(ckeditor_instance)
            CKEDITOR.instances[blockname+lang_target].setData(result.text[0]);
        else
            $('#'+blockname+lang_target).val(result.text[0]);
    });
}

function translateNews(lang_source, lang_target, api_key) {
    var title_source = $('#news_title_'+lang_source).val();
    var text_source = $('#textobject'+lang_source).val();
    if(text_source.length < 1)
        text_source = CKEDITOR.instances['textobject'+lang_source].getData();
    var desc_source = $('#news_desc_'+lang_source).val();
    var keywords_source = $('#keywords_'+lang_source).val();

    if(title_source.length > 0)
        translateText(lang_source, lang_target, title_source, api_key, 'news_title_', false);
    if(text_source.length > 0)
        translateText(lang_source, lang_target, text_source, api_key, 'textobject', true);
    if(desc_source.length > 0)
        translateText(lang_source, lang_target, desc_source, api_key, 'news_desc_', false);
    if(keywords_source.length > 0)
        translateText(lang_source, lang_target, keywords_source, api_key, 'keywords_', false);
}

function translateStatic(lang_source, lang_target, api_key) {
    var title_source = $('#static_title_'+lang_source).val();
    var text_source = $('#textobject'+lang_source).val();
    if(text_source.length < 1)
        text_source = CKEDITOR.instances['textobject'+lang_source].getData();
    var desc_source = $('#static_desc_'+lang_source).val();
    var keywords_source = $('#keywords_'+lang_source).val();

    if(title_source.length > 0)
        translateText(lang_source, lang_target, title_source, api_key, 'static_title_', false);
    if(text_source.length > 0)
        translateText(lang_source, lang_target, text_source, api_key, 'textobject', true);
    if(desc_source.length > 0)
        translateText(lang_source, lang_target, desc_source, api_key, 'static_desc_', false);
    if(keywords_source.length > 0)
        translateText(lang_source, lang_target, keywords_source, api_key, 'keywords_', false);
}