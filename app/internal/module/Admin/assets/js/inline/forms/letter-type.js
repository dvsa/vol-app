OLCS.ready(function () {
  "use strict";

  OLCS.cascadeInput({
    source: "#category",
    dest: "#subCategory",
    url: "/list/document-sub-categories",
    emptyLabel: "Please Select",
  });

  // Section ordering UI
  (function () {
    var $select = $("#sections");
    var $hidden = $("#sectionsOrder");

    if (!$select.length || !$hidden.length) {
      return;
    }

    // Inject minimal styles
    if (!$("#sections-order-styles").length) {
      $("head").append(
        '<style id="sections-order-styles">' +
          ".sections-order-list { list-style: decimal; padding-left: 1.5em; margin: 0.5em 0; }" +
          ".sections-order-list li { padding: 0.4em 0.5em; margin-bottom: 0.25em; background: #f3f2f1; border: 1px solid #b1b4b6; display: flex; align-items: center; justify-content: space-between; }" +
          ".sections-order-list .section-label { flex: 1; }" +
          ".sections-order-list .section-controls { white-space: nowrap; }" +
          ".sections-order-list .section-controls button { padding: 0.15em 0.5em; margin-left: 0.25em; font-size: 0.85em; min-width: auto; }" +
          ".sections-order-container { margin-top: 0.5em; margin-bottom: 1em; }" +
          ".sections-order-container label { font-weight: bold; display: block; margin-bottom: 0.25em; }" +
          "</style>",
      );
    }

    // Create the ordered list container after the sections select wrapper
    var $container = $(
      '<div class="sections-order-container">' +
        '<label class="form-label">Section Order</label>' +
        '<ol id="sections-order-list" class="sections-order-list"></ol>' +
        "</div>",
    );

    // Insert after the Chosen container (which wraps the select)
    var $wrapper = $select.closest(".field");
    if (!$wrapper.length) {
      $wrapper = $select.parent();
    }
    $wrapper.after($container);

    var $list = $("#sections-order-list");

    // Track ordered IDs
    var orderedIds = [];

    // Get option label by value
    function getLabel(id) {
      var $opt = $select.find('option[value="' + id + '"]');
      return $opt.length ? $opt.text() : "Section #" + id;
    }

    // Update hidden input from ordered list
    function syncHidden() {
      orderedIds = [];
      $list.children("li").each(function () {
        orderedIds.push($(this).data("id").toString());
      });
      $hidden.val(orderedIds.join(","));
    }

    // Render the full list from orderedIds
    function renderList() {
      $list.empty();
      for (var i = 0; i < orderedIds.length; i++) {
        appendListItem(orderedIds[i]);
      }
    }

    // Append a single item to the list
    function appendListItem(id) {
      var label = getLabel(id);
      var $li = $(
        '<li data-id="' +
          id +
          '">' +
          '<span class="section-label">' +
          $("<span>").text(label).html() +
          "</span>" +
          '<span class="section-controls">' +
          '<button type="button" class="btn-up govuk-button govuk-button--secondary" title="Move up">&#9650;</button>' +
          '<button type="button" class="btn-down govuk-button govuk-button--secondary" title="Move down">&#9660;</button>' +
          '<button type="button" class="btn-remove govuk-button govuk-button--warning" title="Remove">&#10005;</button>' +
          "</span>" +
          "</li>",
      );
      $list.append($li);
    }

    // Move up
    $list.on("click", ".btn-up", function (e) {
      e.preventDefault();
      var $li = $(this).closest("li");
      var $prev = $li.prev("li");
      if ($prev.length) {
        $li.insertBefore($prev);
        syncHidden();
      }
    });

    // Move down
    $list.on("click", ".btn-down", function (e) {
      e.preventDefault();
      var $li = $(this).closest("li");
      var $next = $li.next("li");
      if ($next.length) {
        $li.insertAfter($next);
        syncHidden();
      }
    });

    // Remove
    $list.on("click", ".btn-remove", function (e) {
      e.preventDefault();
      var $li = $(this).closest("li");
      var id = $li.data("id").toString();

      // Deselect in the multi-select
      $select.find('option[value="' + id + '"]').prop("selected", false);
      $select.trigger("chosen:updated");

      $li.remove();
      syncHidden();
    });

    // Listen for Chosen changes — add newly selected, remove deselected
    $select.on("change", function () {
      var selectedIds = ($select.val() || []).map(String);
      var currentIds = orderedIds.slice();

      // Find newly added IDs (in selectedIds but not in currentIds)
      var added = selectedIds.filter(function (id) {
        return currentIds.indexOf(id) === -1;
      });

      // Find removed IDs (in currentIds but not in selectedIds)
      var removed = currentIds.filter(function (id) {
        return selectedIds.indexOf(id) === -1;
      });

      // Remove deselected items
      for (var i = 0; i < removed.length; i++) {
        $list.children('li[data-id="' + removed[i] + '"]').remove();
      }

      // Append newly added items at the end
      for (var j = 0; j < added.length; j++) {
        appendListItem(added[j]);
      }

      syncHidden();
    });

    // Initialise: if we have a saved order, use it
    var initial = $hidden.val();
    if (initial) {
      orderedIds = initial.split(",").filter(Boolean);
      renderList();
    } else {
      // No saved order — use whatever is selected in the multi-select
      var selected = ($select.val() || []).map(String);
      orderedIds = selected;
      renderList();
      syncHidden();
    }
  })();
});
