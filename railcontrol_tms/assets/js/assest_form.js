// asset_create.php: client-side validation (the server validates again, never trust the browser)
(function () {
  var form = document.getElementById('assetForm');
  if (!form) { return; }

  var today = new Date().toISOString().slice(0, 10);

  var rules = {
    name: function (v) {
      if (v.length < 3 || v.length > 100) { return 'Enter a name of 3 to 100 characters.'; }
      return '';
    },
    type_id: function (v) { return v ? '' : 'Choose an asset type.'; },
    line_name: function (v) { return v ? '' : 'Choose a railway line.'; },
    chainage_km: function (v) {
      return /^\d{1,4}(\.\d)?$/.test(v) ? '' : 'Enter chainage in km, for example 45.2 (0 to 9999.9).';
    },
    latitude: function (v) {
      if (v === '') { return ''; }
      var n = Number(v);
      return (isNaN(n) || n < -90 || n > 90) ? 'Latitude must be between -90 and 90.' : '';
    },
    longitude: function (v) {
      if (v === '') { return ''; }
      var n = Number(v);
      return (isNaN(n) || n < -180 || n > 180) ? 'Longitude must be between -180 and 180.' : '';
    },
    condition_status: function (v) { return v ? '' : 'Choose a condition rating.'; },
    inspected_on: function (v) {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) { return 'Choose the inspection date.'; }
      return v > today ? 'The inspection date cannot be in the future.' : '';
    },
    notes: function (v) { return v.length > 500 ? 'Notes can have at most 500 characters.' : ''; }
  };

  function show(name, msg) {
    var input = form.elements[name];
    var out = document.getElementById('err-' + name);
    if (!input || !out) { return; }
    out.textContent = msg;
    out.hidden = msg === '';
    if (msg) { input.setAttribute('aria-invalid', 'true'); } else { input.removeAttribute('aria-invalid'); }
  }

  function check(name) {
    var input = form.elements[name];
    var msg = rules[name](input.value.trim());
    show(name, msg);
    return msg === '';
  }

  Object.keys(rules).forEach(function (name) {
    var input = form.elements[name];
    if (input) { input.addEventListener('blur', function () { check(name); }); }
  });

  form.addEventListener('submit', function (ev) {
    var firstBad = null;
    Object.keys(rules).forEach(function (name) {
      if (!check(name) && !firstBad) { firstBad = form.elements[name]; }
    });
    var lat = form.elements.latitude.value.trim(), lon = form.elements.longitude.value.trim();
    if (!firstBad && ((lat === '') !== (lon === ''))) {
      show('longitude', 'Enter both latitude and longitude, or leave both empty.');
      firstBad = form.elements.longitude;
    }
    if (firstBad) { ev.preventDefault(); firstBad.focus(); }
  });

  var notes = form.elements.notes, counter = document.getElementById('notesCount');
  if (notes && counter) {
    var update = function () { counter.textContent = notes.value.length + ' / 500'; };
    notes.addEventListener('input', update);
    update();
  }
})();
