/* Blu Core: newsletter forms + scent quiz. */
(function () {
  'use strict';

  /* ----- Newsletter ----- */
  document.querySelectorAll('form.blu-nl').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = form.nextElementSibling;
      var email = form.querySelector('input[type="email"]');
      var btn = form.querySelector('button');
      if (!email.value || email.value.indexOf('@') < 0) {
        msg.textContent = 'Enter an email address.'; msg.className = 'blu-nl__msg is-err'; return;
      }
      btn.disabled = true;
      var fd = new FormData(form);
      fd.append('nonce', window.bluCore ? bluCore.nonce : '');
      fd.append('source', form.dataset.source || 'site');
      fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          msg.innerHTML = res.data && res.data.message ? res.data.message : 'Thanks.';
          msg.className = 'blu-nl__msg ' + (res.success ? 'is-ok' : 'is-err');
          if (res.success) { email.value = ''; }
        })
        .catch(function () { msg.textContent = 'Something went wrong. Try again in a minute.'; msg.className = 'blu-nl__msg is-err'; })
        .finally(function () { btn.disabled = false; });
    });
  });

  /* ----- Scent quiz ----- */
  document.querySelectorAll('.blu-quiz').forEach(function (quiz) {
    var products = {};
    try { products = JSON.parse(quiz.dataset.products || '{}'); } catch (e) {}
    var answers = {};
    var steps = quiz.querySelectorAll('.blu-quiz__q');
    var result = quiz.querySelector('.blu-quiz__result');

    function pick() {
      var a = answers;
      var key = 'original';
      if (a[0] === 'yes') {
        key = (a[2] === 'avoid' || a[1] === 'none') ? 'unscented' : 'bsf';
        if (a[2] === 'avoid' && a[1] !== 'none') { key = 'eofree'; }
      } else if (a[2] === 'avoid') {
        key = a[1] === 'none' ? 'unscented' : (a[1] === 'warm' ? 'blakbird3' : 'eofree');
      } else if (a[1] === 'warm') {
        key = 'blakbird';
      } else if (a[1] === 'light') {
        key = 'cedar';
      } else if (a[1] === 'none') {
        key = 'unscented';
      }
      if (a[3] === 'jar' && (key === 'original' || key === 'cedar')) { key = 'jar'; }
      return products[key] || products.original;
    }

    function show(step) {
      steps.forEach(function (s) { s.classList.toggle('is-active', parseInt(s.dataset.step, 10) === step); });
      if (step >= steps.length) {
        var p = pick();
        result.querySelector('.blu-quiz__rtitle').textContent = p.title;
        result.querySelector('.blu-quiz__rwhy').textContent = p.why;
        result.querySelector('.blu-quiz__rlink').href = p.url;
        result.querySelector('.blu-quiz__rlink').textContent = 'See ' + p.title;
        result.classList.add('is-active');
      } else {
        result.classList.remove('is-active');
      }
    }

    quiz.querySelectorAll('.blu-quiz__opts button').forEach(function (b) {
      b.addEventListener('click', function () {
        answers[parseInt(b.dataset.q, 10)] = b.dataset.v;
        show(parseInt(b.dataset.q, 10) + 1);
      });
    });
    quiz.querySelector('.blu-quiz__again').addEventListener('click', function () { answers = {}; show(0); });
  });
})();
