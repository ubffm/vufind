/*global VuFind */
var dprovs = {
  'AdamMatthewDigital':'amd',
  'AkademiederKünsteBerlin':'adk',
  'AkademiederKünsteBerlin,Archivbestand':'adka',
  'AkademiederKünsteBerlin,ArchivDarstellendeKunst':'adka',
  'AlexanderStreetPress':'asp',
  'ArchitekturmuseumBerlin': 'tbs',
  'BadischeLandesbibliothek':'blb',
  'BASE-BielefeldAcademicSearchEngine':'base',
  'DerradeMorodaDanceArchives':'ddm',
  'DeutschesForumfürFigurentheaterundPuppenspielkunst':'fdn',
  'DeutschesTanzfilminstitutBremen':'dtb',
  'DeutschesTanzarchivKöln':'dtk',
  'DeutschesTheatermuseumMünchen':'dtm',
  'Universitäts-undLandesbibliothekDüsseldorf':'dtz',
  'UniversitätsbibliothekFrankfurtamMain':'fub',
  'DonJuanArchivWien':'kom',
  'GottfriedWilhelmLeibnizBibliothek':'gwlb',
  'InternationalesTheaterinstitut–MimeCentruminInternationalesTheaterinstitut–MediathekfürTanzundTheater(MTT)':'mcb',
  'OnlineContents':'olc',
  'TheaterwissenschaftlicheSammlungderUniversitätzuKöln':'slw',
  'SchweizerischeTheatersammlunginStiftungSAPA,SchweizerArchivderDarstellendenKünste':'sts',
  'TanzarchivLeipzig':'tal',
  'Architekturmuseum(Berlin)':'tbs',
  'TeatroEspañoldelSiglodeOro':'tes',
  'TanzfondsErbe':'tfe',
  'Projekt„TheaterundMusikinWeimar1754-1990“':'thulb',
  'TheatermuseumderLandeshauptstadtDüsseldorf':'tmd',
  'transcriptVerlag':'tra',
  'FreieUniversitätBerlin,InstitutfürTheaterwissenschaft,TheaterhistorischeSammlungen':'tsb',
  'VerbundDeutscherTanzarchive':'vdt'
};

/*exported initFacetTree */
/* locally CHANGED:
 * - many changes in buildFacetTree (l. 82)
 * - added more param in functions that call tree building
 * - removed selector boxes in hierarchical facet
 */
function buildFacetNodes(data, currentPath, allowExclude, excludeTitle, counts, dprovs)
{
  var json = [];
  var selected = VuFind.translate('Selected');
  var separator = VuFind.translate('number_thousands_separator');

  for (var i = 0; i < data.length; i++) {
    var facet = data[i];
    var html = document.createElement('div');
    html.className = 'facet';

    var url = currentPath + facet.href;
    var excludeUrl = currentPath + facet.exclude;
    var item = document.createElement('span');
    item.className = 'text';
    if (facet.isApplied) {
      item.className += ' applied';
    }
    item.setAttribute('title', facet.displayText);
    if (facet.operator === 'OR') {
      item.innerHTML = facet.isApplied ? VuFind.icon('facet-checked', { title: selected, class: 'icon-link__icon' }) : VuFind.icon('facet-unchecked', 'icon-link__icon');
    }
    var facetValue = document.createElement('span');
    facetValue.className = 'facet-value icon-link__label';
    facetValue.appendChild(document.createTextNode(facet.displayText));
    item.appendChild(facetValue);
    html.appendChild(item);

    var children = null;
    if (typeof facet.children !== 'undefined' && facet.children.length > 0) {
      children = buildFacetNodes(facet.children, currentPath, allowExclude, excludeTitle, counts, dprovs);
    }

    var dprov = '';
    var normText = facet.displayText.replace(/(\n|\s)/gm,"");
    if (dprovs.hasOwnProperty(normText)) {
      dprov = dprovs[normText];
    }

    json.push({
      text: html.outerHTML,
      children: children,
      state: {
        opened: facet.hasAppliedChildren,
        selected: facet.isApplied
      },
      a_attr: {
        class: 'hierarchical-facet-anchor icon-link',
        href: url
      },
      li_attr: facet.isApplied ? { 
        class: 'active' 
      } : {},
      dprov: dprov,
      data: {
        url: url.replace(/&amp;/g, '&'),
        count: !facet.isApplied && counts && facet.count
          ? facet.count.toString().replace(/\B(?=(\d{3})+\b)/g, separator) : null,
        excludeUrl: allowExclude && !facet.isApplied ? excludeUrl : '',
        excludeTitle: excludeTitle
      }
    });
  }

  return json;
}

function buildFacetTree(treeNode, facetData, inSidebar) {
  // Enable keyboard navigation also when a screen reader is active
  treeNode.bind('select_node.jstree', VuFind.sideFacets.showLoadingOverlay);

  var currentPath = treeNode.data('path');
  var allowExclude = treeNode.data('exclude');
  var excludeTitle = treeNode.data('exclude-title');

  var results = buildFacetNodes(facetData, currentPath, allowExclude, excludeTitle, inSidebar, dprovs);
  treeNode.find('.loading-spinner').parent().remove();
  if (inSidebar) {
    treeNode.on('loaded.jstree open_node.jstree', function treeLoad(/*e, data*/) {
      var treeItems = treeNode.find('ul.jstree-container-ul > li.jstree-node');
      treeNode.find('a.exclude').click(VuFind.sideFacets.showLoadingOverlay);
      treeItems.addClass('list-group-item');
      if (treeNode.parent().hasClass('truncate-hierarchy')) {
        VuFind.truncate.initTruncate(treeNode.parent(), '.list-group-item');
        if (treeItems.find('div.flex').length === 0) {
          treeItems.each(function addInfo(i) {
            if (results[i].dprov) {
              var flex = addFlex(results[i].dprov, this.children[1]);
              this.insertBefore(flex,this.children[1]);
              if (this.children[2]) {
                var treeChildren = treeItems.find('ul.jstree-children > li.jstree-node');
                treeChildren.each(function addInfo(j) {
                  if (results[i].children[j].dprov) {
                    var flex = addFlex(results[i].children[j].dprov, this.children[1]);
                    this.insertBefore(flex,this.children[1]);
                  }
               });
             }
             }
        }); 
        } else if(treeItems.find('ul.jstree-children')) {
          var treeChildren = treeItems.find('ul.jstree-children > li.jstree-node');
          var parentId = treeChildren.parent()[0].parentNode.id;
          if (treeChildren.find('div.flex').length === 0) {
            treeChildren.each(function addInfo(j) {
              var index = Array.prototype.findIndex.call(treeItems,(x) => x.id === parentId);              
              if (index in results && j in results[index].children && results[index].children[j].dprov) {                  
                  var flex = addFlex(results[index].children[j].dprov, this.children[1]);
                  this.insertBefore(flex,this.children[1]);
              }
            });
          }
        }
      }
      // reinit lightbox
      VuFind.init('lightbox');
    });
  }
  treeNode.jstree({
    'core': {
      'data': results
    },
    'plugins': ['vufindFacet']
  });
}

function addFlex(dprov,child)
{
  var flex = document.createElement('div');
  flex.className = 'flex';
  var provInfo = document.createElement('a');
  provInfo.className = 'providerInfo';
  provInfo.href = VuFind.path + '/DataProvider/' + dprov;
  provInfo.setAttribute('data-lightbox','');
  var infoIcon = document.createElement('i');
  infoIcon.className = 'fa fa-info-circle';
  infoIcon.setAttribute('aria-hidden', 'true');
  provInfo.appendChild(infoIcon);
  flex.appendChild(provInfo);     
  flex.appendChild(child);
  return flex;
}

function loadFacetTree(treeNode, inSidebar)
{
  var loaded = treeNode.data('loaded');
  if (loaded) {
    return;
  }
  treeNode.data('loaded', true);

  if (inSidebar) {
    treeNode.prepend('<li class="jstree-node list-group-item facet-load-indicator">' + VuFind.loading() + '</li>');
  } else {
    treeNode.prepend('<div>' + VuFind.loading() + '<div>');
  }
  var request = {
    method: "getFacetData",
    source: treeNode.data('source'),
    facetName: treeNode.data('facet'),
    facetSort: treeNode.data('sort'),
    facetOperator: treeNode.data('operator'),
    query: treeNode.data('query'),
    querySuppressed: treeNode.data('querySuppressed'),
    extraFields: treeNode.data('extraFields')
  };
  $.getJSON(VuFind.path + '/AJAX/JSON?' + request.query,
    request,
    function getFacetData(response/*, textStatus*/) {
      buildFacetTree(treeNode, response.data.facets, inSidebar);
    }
  );
}

function initFacetTree(treeNode, inSidebar)
{
  // Defer init if the facet is collapsed:
  let $collapse = treeNode.parents('.facet-group').find('.collapse');
  if (!$collapse.hasClass('in')) {
    $collapse.on('show.bs.collapse', function onExpand() {
      loadFacetTree(treeNode, inSidebar);
    });
    return;
  } else {
    loadFacetTree(treeNode, inSidebar);
  }
}

/* --- Side Facets --- */
VuFind.register('sideFacets', function SideFacets() {
  function showLoadingOverlay(e, data) {
    e.preventDefault();
    var overlay = '<div class="facet-loading-overlay">'
      + '<span class="facet-loading-overlay-label">'
      + VuFind.loading()
      + "</span></div>";
    $(this).closest(".collapse").append(overlay);
    if (typeof data !== "undefined") {
      // Remove jstree-clicked class from JSTree links to avoid the color change:
      data.instance.get_node(data.node, true).children().removeClass('jstree-clicked');
    }
    // This callback operates both as a click handler and a JSTree callback;
    // if the data element is undefined, we assume we are handling a click.
    var href = typeof data === "undefined" || typeof data.node.data.url === "undefined"
      ? $(this).attr('href') : data.node.data.url;
    window.location.assign(href);
    return false;
  }

  function activateFacetBlocking(context) {
    var finalContext = (typeof context === "undefined") ? $(document.body) : context;
    finalContext.find('a.facet:not(.narrow-toggle),.facet a').click(showLoadingOverlay);
  }

  function activateSingleAjaxFacetContainer() {
    var $container = $(this);
    var facetList = [];
    var $facets = $container.find('div.collapse.in[data-facet], .checkbox-filter[data-facet]');
    $facets.each(function addFacet() {
      if (!$(this).data('loaded')) {
        facetList.push($(this).data('facet'));
      }
    });
    if (facetList.length === 0) {
      return;
    }
    var request = {
      method: 'getSideFacets',
      searchClassId: $container.data('searchClassId'),
      location: $container.data('location'),
      configIndex: $container.data('configIndex'),
      query: $container.data('query'),
      querySuppressed: $container.data('querySuppressed'),
      extraFields: $container.data('extraFields'),
      enabledFacets: facetList
    };
    $container.find('.facet-load-indicator').removeClass('hidden');
    $.getJSON(VuFind.path + '/AJAX/JSON?' + request.query, request)
      .done(function onGetSideFacetsDone(response) {
        $.each(response.data.facets, function initFacet(facet, facetData) {
          var containerSelector = typeof facetData.checkboxCount !== 'undefined'
            ? '.checkbox-filter' : ':not(.checkbox-filter)';
          var $facetContainer = $container.find(containerSelector + '[data-facet="' + facet + '"]');
          $facetContainer.data('loaded', 'true');
          if (typeof facetData.checkboxCount !== 'undefined') {
            if (facetData.checkboxCount !== null) {
              $facetContainer.find('.avail-count').text(
                facetData.checkboxCount.toString().replace(/\B(?=(\d{3})+\b)/g, VuFind.translate('number_thousands_separator'))
              );
            }
          } else if (typeof facetData.html !== 'undefined') {
            $facetContainer.html(VuFind.updateCspNonce(facetData.html));
            activateFacetBlocking($facetContainer);
          } else {
            var treeNode = $facetContainer.find('.jstree-facet');
            VuFind.emit('VuFind.sidefacets.treenodeloaded', {node: treeNode});

            buildFacetTree(treeNode, facetData.list, true);
          }
          $facetContainer.find('.facet-load-indicator').remove();
        });
        VuFind.lightbox.bind($('.sidebar'));
        VuFind.emit('VuFind.sidefacets.loaded');
      })
      .fail(function onGetSideFacetsFail() {
        $container.find('.facet-load-indicator').remove();
        $container.find('.facet-load-failed').removeClass('hidden');
      });
  }

  function loadAjaxSideFacets() {
    $('.side-facets-container-ajax').each(activateSingleAjaxFacetContainer);
  }

  function facetSessionStorage(e) {
    var source = $('#result0 .hiddenSource').val();
    var id = e.target.id;
    var key = 'sidefacet-' + source + id;
    if (!sessionStorage.getItem(key)) {
      sessionStorage.setItem(key, document.getElementById(id).className);
    } else {
      sessionStorage.removeItem(key);
    }
  }

  function init() {
    // Display "loading" message after user clicks facet:
    activateFacetBlocking();

    // Side facet status saving
    $('.facet-group .collapse').each(function openStoredFacets(index, item) {
      var source = $('#result0 .hiddenSource').val();
      var storedItem = sessionStorage.getItem('sidefacet-' + source + item.id);
      if (storedItem) {
        var saveTransition = $.support.transition;
        try {
          $.support.transition = false;
          if ((' ' + storedItem + ' ').indexOf(' in ') > -1) {
            $(item).collapse('show');
          } else if (!$(item).data('forceIn')) {
            $(item).collapse('hide');
          }
        } finally {
          $.support.transition = saveTransition;
        }
      }
    });
    $('.facet-group').on('shown.bs.collapse', facetSessionStorage);
    $('.facet-group').on('hidden.bs.collapse', facetSessionStorage);

    // Side facets loaded with AJAX
    $('.side-facets-container-ajax')
      .find('div.collapse[data-facet]:not(.in)')
      .on('shown.bs.collapse', function expandFacet() {
        loadAjaxSideFacets();
      });
    loadAjaxSideFacets();

    // Keep filter dropdowns on screen
    $(".search-filter-dropdown").on("shown.bs.dropdown", function checkFilterDropdownWidth(e) {
      var $dropdown = $(e.target).find(".dropdown-menu");
      if ($(e.target).position().left + $dropdown.width() >= window.innerWidth) {
        $dropdown.addClass("dropdown-menu-right");
      } else {
        $dropdown.removeClass("dropdown-menu-right");
      }
    });
  }

  return { init: init, showLoadingOverlay: showLoadingOverlay };
});

/* --- Lightbox Facets --- */
VuFind.register('lightbox_facets', function LightboxFacets() {
  function lightboxFacetSorting() {
    var sortButtons = $('.js-facet-sort');
    function sortAjax(button) {
      var sort = $(button).data('sort');
      var list = $('#facet-list-' + sort);
      if (list.find('.js-facet-item').length === 0) {
        list.find('.js-facet-next-page').html(VuFind.translate('loading_ellipsis'));
        $.ajax(button.href + '&layout=lightbox')
          .done(function facetSortTitleDone(data) {
            list.prepend($('<span>' + data + '</span>').find('.js-facet-item'));
            list.find('.js-facet-next-page').html(VuFind.translate('more_ellipsis'));
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
      button.html(VuFind.translate('loading_ellipsis'));
      $.ajax(this.href + '&layout=lightbox')
        .done(function facetLightboxMoreDone(data) {
          var htmlDiv = $('<div>' + data + '</div>');
          var list = htmlDiv.find('.js-facet-item');
          button.before(list);
          if (list.length && htmlDiv.find('.js-facet-next-page').length) {
            button.attr('data-page', page + 1);
            button.attr('href', button.attr('href').replace(/facetpage=\d+/, 'facetpage=' + (page + 1)));
            button.html(VuFind.translate('more_ellipsis'));
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

function registerSideFacetTruncation() {
  VuFind.truncate.initTruncate('.truncate-facets', '.facet');
}

VuFind.listen('VuFind.sidefacets.loaded', registerSideFacetTruncation);
