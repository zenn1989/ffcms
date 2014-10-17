{% macro checkjs(initobject, checkarray) %}
    <script>
        var check_target_class = $('{{ checkarray }}');
        $('{{ initobject }}').click(function () {
            $(check_target_class).each(function () {
                $(this).prop('checked', !$(this).is(':checked'));
                console.log($(this));

            });
        });
    </script>
{% endmacro %}