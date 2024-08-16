$(function () {
    "use strict";

    var searchForm = $("#translationSearchForm");
    var searchBox = $("#translationSearch");
    var searchResults = $("#keySearchResults");
    var autcompleteDiv = $("#keyAutocomplete");
    var jsonBaseUrl = $("#jsonBaseUrl").val();
    const urlParams = new URLSearchParams(window.location.search);

    var delayAjax = (function () {
        var timer = 0;
        return function (callback) {
            clearTimeout(timer);
            timer = setTimeout(callback, 1200);
        };
    })();

    function showAutocomplete()
    {
        var pos = searchBox.position();
        var height = searchBox.outerHeight();
        autcompleteDiv.css({
            position: "absolute",
            top: (pos.top + height + 10) + "px",
            left: (pos.left + 10) + "px"
        }).show();
        hideAutocompleteDiv("#keyAutocomplete");
    }

    // On pageload, pre-populate the searchbox with the last term GET'ed.
    if (urlParams.has("translationSearch")) {
        searchBox.val(urlParams.get("translationSearch"));
    }

    // When search form is submitted, set the tranlationSearch GET param and refresh to browser
    searchForm.submit(function (event) {
        urlParams.set("translationSearch", searchBox.val());
        window.location.search = urlParams.toString();
        event.preventDefault();
    });

    searchBox.keyup(function (e) {
        if (e.keyCode === 27) {
            autcompleteDiv.toggle();
        }
    });

    // Autocomplete functionality for the key search box
    searchBox.keypress(function (event) {
        if (searchBox.val().length > 2) {
            delayAjax(function () {
                $.get(
                    jsonBaseUrl + "xhrsearch",
                    {translationSearch: searchBox.val()}
                ).done(function (data) {
                        searchResults.empty();
                        $.each(data.results, function (index, result) {
                            searchResults.append("<div class=\"translationAcRow\"><strong><a class=\"govuk-link\" href=\"" + jsonBaseUrl + "details/" + result.id + "\">" + result.id + "</a></strong><br>" + result.description + "</div>");
                        });
                        showAutocomplete();
                });
            });
        }
    });

    // Event listener to detect if user clicks outside of auto-complete list to dismiss results pane
    function hideAutocompleteDiv(selector)
    {
        const outsideClickListener = (event) => {
            const $target = $(event.target);
            if (!$target.closest(selector).length && $(selector).is(":visible")) {
                $(selector).hide();
                removeClickListener();
            }
        };

        const removeClickListener = () => {
            document.removeEventListener('click', outsideClickListener);
        };

        document.addEventListener('click', outsideClickListener);
    }
});
