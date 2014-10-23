{% macro selectize(url) %}
    <script src="{{ url }}/resource/selectize/0.11.2/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="{{ url }}/resource/selectize/0.11.2/css/selectize.bootstrap3.css" />
    <script>
        (function( $ ) {
            "use strict";

            $( document ).ready(function() {
                $('.selectize-select').selectize({
                    create: false,
                    sortField: 'text'
                });
                $('.selectize-tags').selectize({
                    plugins: ['remove_button'],
                    delimiter: ',',
                    persist: false,
                    create: function (input) {
                        return {
                            value: input,
                            text: input
                        }
                    }
                });
            });

        })( jQuery );
    </script>
{% endmacro %}
{% macro switch(url) %}
    <link rel="stylesheet" href="{{ url }}/resource/bootstrap-switch/3.0/css/bootstrap3/bootstrap-switch.css">
    <script src="{{ url }}/resource/bootstrap-switch/3.0/js/bootstrap-switch.min.js"></script>
    <script>
        (function( $ ) {
            "use strict";

            $( document ).ready(function() {
                $(".switchable").bootstrapSwitch({
                    onColor: 'success',
                    offColor: 'danger'
                });
            });

        })( jQuery );
    </script>
{% endmacro %}