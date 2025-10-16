<script>
  (function(){
    function sendAiRequest(params, cb, errCb){
      var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      fetch('/admin/ai/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: JSON.stringify(params)
      }).then(function(res){
        return res.json().then(function(json){
          if(!res.ok){ throw json; }
          cb(json);
        });
      }).catch(function(e){ errCb(e); });
    }

    document.addEventListener('click', function(e){
      var btn = e.target.closest && e.target.closest('.ai-generate-btn');
      if(!btn) return;
      var field = btn.getAttribute('data-field');
      var type = btn.getAttribute('data-type') || 'summary';
      var title = btn.getAttribute('data-title') || '';
      var textarea = document.getElementById(field);
  if(!textarea) return alert('{{ trans("app.target_field_not_found") }}'.replace(':field', field));

      btn.disabled = true;
  var originalText = btn.innerText;
  var genText = '{{ trans("app.generating") }}';
  btn.innerText = genText;

      sendAiRequest({ field: field, type: type, title: title }, function(res){
        if(res.status && res.data){
          // insert into textarea (replace or append based on type)
          textarea.value = res.data;
          if(typeof $(textarea).summernote === 'function'){
            $(textarea).summernote('code', res.data);
          }
        } else {
          alert(res.message || '{{ trans("app.no_content_returned_from_ai") }}');
        }
        btn.disabled = false;
        btn.innerText = originalText;
      }, function(err){
  var msg = (err && err.message) ? err.message : '{{ trans("app.error_generating_content") }}';
        alert(msg);
        btn.disabled = false;
        btn.innerText = originalText;
      });
    });
  })();
</script>
