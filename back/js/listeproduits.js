$(function() {
    /* = Gestion de la disponibilité des produits
    `------------------------------------------------- */
    $("a.disponible").live("click", function() {
        var $this = $(":checkbox", this),
            button = $("i", $(this)),
            value = $this.val().split("|"),
            id_gab_page = parseInt(value[0]),
            id_version = parseInt(value[1]);

        if ($this.is(":checked")) {
            $this.removeAttr("checked");
        } else {
            $this.attr("checked", "checked");
        }
        var checked = $this.is(':checked');
        $.post(
            'back/produits/disponible.html',
            {
                id_gab_page: id_gab_page,
                id_version: id_version,
                disponible: checked ? 1 : 0
            },
            function(data) {
                if (data.status !== 'success') {
                    $this.attr('checked', !checked);
                    $.sticky("Une erreur est survenue", {
                        type: "error"
                    });
                } else {
                    if (checked) {
                        button.html("oui");
                        $.sticky("Le produit a été rendue disponible", {
                            type: "success"
                        });
                    } else {
                        button.html("non");
                        $.sticky("Le produit a été rendue indisponible", {
                            type: "success"
                        });
                    }
                }
            },
            'json'
        );
    });
});
