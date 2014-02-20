{% macro checkjs(initobject, checkarray) %}
    <script>
        $('{{ initobject }}').click(function () {
            if ($('{{ checkarray }}').is(':checked'))
                $('{{ checkarray }}').attr('checked', false);
            else
                $('{{ checkarray }}').attr('checked', true);
        });
    </script>
{% endmacro %}