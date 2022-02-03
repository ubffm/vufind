function addTotalResults(id,url) {
  $.getJSON(VuFind.path + '/AJAX/JSON?' + url.split('?')[1],
     {method: "GetTotalResults",searchClassId: id},
     function (data) {
      $("#" + id + " > a").append(data['data']['localizedTotal']);
    });
}
