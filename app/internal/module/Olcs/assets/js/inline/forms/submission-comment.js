$(function() {
    "use strict";

    $(".tinymce").tinymce({
        menubar : false,
        document_base_url: "/tinymce/",
        browser_spellcheck : true,
        statusbar : false,
        spellchecker_languages : "+English=en",
        height : 260,
        content_css : "/tinymce/skins/lightgray/custom.css",
        style_formats: [
            {title: "Headers", items: [
                {title: "Header 1", format: "h1"},
                {title: "Header 2", format: "h2"},
                {title: "Header 3", format: "h3"}
            ]},
            {title: "Inline", items: [
                {title: "Bold", icon: "bold", format: "bold"},
                {title: "Italic", icon: "italic", format: "italic"},
                {title: "Underline", icon: "underline", format: "underline"},
                {title: "Strikethrough", icon: "strikethrough", format: "strikethrough"}
            ]}
        ],
        plugins: [
            "lists charmap",
            "searchreplace",
            "contextmenu paste spellchecker"
        ],
        toolbar: "styleselect | bold italic | bullist numlist"
    });

});
