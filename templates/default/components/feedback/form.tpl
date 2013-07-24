<h3>{$lang::feedback_form_title}</h3>
<hr />
<p>{$lang::feedback_form_desc}</p>
{$notify}
<form acton="" method="post" class="form-horizontal">
    <fieldset>
        <div class="control-group">
            <label class="control-label">{$lang::feedback_form_name_label}</label>

            <div class="controls">
                <input type="text" name="topic_name" class="input-large" required/>
                <p class="help-block">{$lang::feedback_form_name_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::feedback_form_title_label}</label>

            <div class="controls">
                <input type="text" name="topic_title" class="input-large" required/>
                <p class="help-block">{$lang::feedback_form_title_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::feedback_form_text_label}</label>

            <div class="controls">
                <textarea name="topic_body" style="width: 90%;height: 200px;" placeholder="Message ..."></textarea>
                <p class="help-block">{$lang::feedback_form_text_desc}</p>
            </div>
        </div>
        {$if !user.auth}
        <div class="control-group">
            <label class="control-label">{$lang::feedback_form_email_label}</label>

            <div class="controls">
                <input type="topic_email" name="topic_email" class="input-large" required/>
                <p class="help-block">{$lang::feedback_form_email_desc}</p>
            </div>
        </div>
        {$/if}
        <div class="control-group">
            <label class="control-label">{$lang::feedback_form_captcha_label}</label>

            <div class="controls">
                <img src="{$captcha}" id="captcha"/><a href="#" onclick="document.getElementById('captcha').src='{$captcha}?'+Math.random();"><i
                            class="icon-refresh"></i></a><br/>
                <input type="text" name="captcha" required>
                <p class="help-block">{$lang::feedback_form_captcha_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <input type="submit" name="dofeedback" value="{$lang::feedback_form_send_button}" class="btn btn-danger" />
            </div>
        </div>
        </fieldset>
</form>