/*global htmlEncode, VuFind */
/*exported initFacetTree */
function buildFacetNodes(data, currentPath, allowExclude, excludeTitle, counts, dprovs)
{
  var json = [];

  $(data).each(function facetNodesEach() {
    var html = '';
    if (!this.isApplied && counts) {
      html = '<span class="badge" style="float: right">' + this.count.toString().replace(/\B(?=(\d{3})+\b)/g, VuFind.translate('number_thousands_separator'));
      if (allowExclude) {
        var excludeURL = currentPath + this.exclude;
        excludeURL.replace("'", "\\'");
        // Just to be safe
        html += ' <a href="' + excludeURL + '" onclick="document.location.href=\'' + excludeURL + '\'; return false;" title="' + htmlEncode(excludeTitle) + '"><i class="fa fa-times" title="' + VuFind.translate('Selected') + '"></i></a>';
      }
      html += '</span>';
    }

    var url = currentPath + this.href;
    // Just to be safe
    url.replace("'", "\\'");
    html += '<span class="main' + (this.isApplied ? ' applied' : '') + '" title="' + htmlEncode(this.displayText) + '"'
      + ' onclick="document.location.href=\'' + url + '\'; return false;">';
    if (this.isApplied) {
      html += '<i class="fa fa-check pull-right" title="' + VuFind.translate('Selected') + '"></i>';
    }
    html += ' ' + this.displayText;
    html += '</span>';

    var children = null;
    if (typeof this.children !== 'undefined' && this.children.length > 0) {
      children = buildFacetNodes(this.children, currentPath, allowExclude, excludeTitle, counts, dprovs);
    }

    var dprov = '';
    if (dprovs.hasOwnProperty(this.displayText)) {
      dprov = dprovs[this.displayText];
    }

    json.push({
      'text': html,
      'children': children,
      'applied': this.isApplied,
      'state': {
        'opened': this.hasAppliedChildren
      },
      'li_attr': this.isApplied ? { 'class': 'active' } : {},
      'dprov': dprov
    });

  });
  return json;
}

function initFacetTree(treeNode, inSidebar)
{
  var loaded = treeNode.data('loaded');
  if (loaded) {
    return;
  }
  treeNode.data('loaded', true);

  var facet = treeNode.data('facet');
  var operator = treeNode.data('operator');
  var currentPath = treeNode.data('path');
  var allowExclude = treeNode.data('exclude');
  var excludeTitle = treeNode.data('exclude-title');
  var sort = treeNode.data('sort');
  var query = window.location.href.split('?')[1];

  if (inSidebar) {
    treeNode.prepend('<li class="list-group-item"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></li>');
  } else {
    treeNode.prepend('<div><i class="fa fa-spinner fa-spin" aria-hidden="true"></i><div>');
  }
  $.getJSON(VuFind.path + '/AJAX/JSON?' + query,
    {
      method: "getFacetData",
      facetName: facet,
      facetSort: sort,
      facetOperator: operator
    },
    function getFacetData(response/*, textStatus*/) {
      if (response.status === "OK") {
        var dprovs = {
          'Akademie der Künste Berlin':'adk',
          'Akademie der Künste Berlin, Archiv Darstellende Kunst':'adka',
          'Alexander Street Press':'asp',
          'Deutsches Tanzfilminstitut Bremen':'dtb',
          'Deutsches Tanzarchiv Köln':'dtk',
          'Deutsches Theatermuseum München':'dtm',
          'Universitäts- und Landesbibliothek Düsseldorf':'dtz',
          'Universitätsbibliothek Frankfurt am Main':'fub',
          'Don Juan Archiv Wien':'kom',
          'Mime Centrum Berlin':'mcb',
          'Online Contents':'olc',
          'Theaterwissenschaftliche Sammlung der Universität zu Köln':'slw',
          'Schweizerische Theatersammlung':'sts',
          'Tanzarchiv Leipzig':'tal',
          'Teatro Español del Siglo de Oro':'tes',
          'Tanzfonds Erbe':'tfe',
          'Theatermuseum der Landeshauptstadt Düsseldorf':'tmd',
          'transcript Verlag':'tra',
          'Freie Universität Berlin, Institut für Theaterwissenschaft, Theaterhistorische Sammlungen':'tsb',
          'Verbund Deutscher Tanzarchive':'vdt'
        };
        var results = buildFacetNodes(response.data, currentPath, allowExclude, excludeTitle, inSidebar, dprovs);
        treeNode.find('.fa-spinner').parent().remove();
        if (inSidebar) {

          treeNode.on('loaded.jstree', function treeLoad(/*e, data*/) {
            var treeItems = treeNode.find('ul.jstree-container-ul > li.jstree-node');
            $(treeItems).addClass('list-group-item');
            $(treeItems).each(function addInfo(i) {
              if (results[i].dprov) {
                $(this).append('<a data-lightbox href="/vufind/dprovider/' + results[i].dprov + '"><i class="fa fa-info-circle" aria-hidden="true"></i></a>');
              }
            });
          });
          treeNode.on('open_node.jstree', function treeNodeOpen(e,data) {
            var treeItems = treeNode.find('ul.jstree-container-ul > li.jstree-node');
            $(treeItems).addClass('list-group-item');
            $(treeItems).each(function addInfo(i) {
              if (this.id == data.node.id) {
                // add info to parent node
                if (results[i].dprov) {
                  $(this.children[1]).after('<a data-lightbox href="/vufind/dprovider/' + results[i].dprov + '"><i class="fa fa-info-circle" aria-hidden="true"></i></a>');
                }
                var treeChildren = $(this).find('ul.jstree-children > li.jstree-node');
                // add info to each child
                data.node.children.forEach(function(child,j) {
                  if (results[i].children[j].dprov) {
                    $(treeChildren[j]).append('<a data-lightbox href="/vufind/dprovider/' + results[i].children[j].dprov + '"><i class="fa fa-info-circle" aria-hidden="true"></i></a>');
                  }
                });
              }
            });
          });
        }
        treeNode.jstree({
          'core': {
            'data': results
          }
        });
      }
    }
  );
}

/* --- Lightbox Facets --- */
VuFind.register('lightbox_facets', function LightboxFacets() {
  function lightboxFacetSorting() {
    var sortButtons = $('.js-facet-sort');
    function sortAjax(button) {
      var sort = $(button).data('sort');
      var list = $('#facet-list-' + sort);
      if (list.find('.js-facet-item').length === 0) {
        list.find('.js-facet-next-page').text(VuFind.translate('loading') + '...');
        $.ajax(button.href + '&layout=lightbox')
          .done(function facetSortTitleDone(data) {
            list.prepend($('<span>' + data + '</span>').find('.js-facet-item'));
            list.find('.js-facet-next-page').text(VuFind.translate('more') + ' ...');
          });
      }
      $('.full-facet-list').addClass('hidden');
      list.removeClass('hidden');
      sortButtons.removeClass('active');
    }
    sortButtons.click(function facetSortButton() {
      sortAjax(this);
      $(this).addClass('active');
      return false;
    });
  }

  function setup() {
    lightboxFacetSorting();
    $('.js-facet-next-page').click(function facetLightboxMore() {
      var button = $(this);
      var page = parseInt(button.attr('data-page'), 10);
      if (button.attr('disabled')) {
        return false;
      }
      button.attr('disabled', 1);
      button.text(VuFind.translate('loading') + '...');
      $.ajax(this.href + '&layout=lightbox')
        .done(function facetLightboxMoreDone(data) {
          var htmlDiv = $('<div>' + data + '</div>');
          var list = htmlDiv.find('.js-facet-item');
          button.before(list);
          if (list.length && htmlDiv.find('.js-facet-next-page').length) {
            button.attr('data-page', page + 1);
            button.attr('href', button.attr('href').replace(/facetpage=\d+/, 'facetpage=' + (page + 1)));
            button.text(VuFind.translate('more') + ' ...');
            button.removeAttr('disabled');
          } else {
            button.remove();
          }
        });
      return false;
    });
    var margin = 230;
    $('#modal').on('show.bs.modal', function facetListHeight() {
      $('#modal .lightbox-scroll').css('max-height', window.innerHeight - margin);
    });
    $(window).resize(function facetListResize() {
      $('#modal .lightbox-scroll').css('max-height', window.innerHeight - margin);
    });
  }

  return { setup: setup };
});
