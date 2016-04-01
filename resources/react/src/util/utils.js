export function storeLabels(labels, optBlock) {
  const field = optBlock.key;
  labels[field] = {};
  if(Array.isArray(optBlock.options)) {
    optBlock.options.forEach((option) => {
      labels[field][option.value] = option.name;
    });
  }

  return labels;
}

export function getLabel(labels, field, value) {
  return labels && labels[field] && labels[field][value] || '';
}

// query ex : { a11: [1, 3, 5], o2: [2, 3, 6] }
export function inQuery(query, field, value) {
  return query
      && Array.isArray(query[field])
      && query[field].indexOf(value) !== -1;
}

export function toggleInQuery(query, field, value) {
  if (!Array.isArray(query[field])) {
    query[field] = [];
  }

  if (query[field].indexOf(value) === -1) {
    query[field].push(value);
  } else {
    query[field].splice(query[field].indexOf(value), 1);
  }

  console.log('- toggleInQuery', query);

  return query;
}

export function changeInQuery(query, field, value) {
  if (!Array.isArray(query[field])) {
    query[field] = [];
  }
  if( query[field].length > 0) {
    query[field].splice(query[field].indexOf(value), 1);
  }
  query[field].push(value);

  console.log('- changeInQuery', query);

  return query;
}

export function pageList(result) {
	const { total_cnt, total_page, page, count } = result;
	const page_num = 10;
	const s_page = ( parseInt( ( page - 1 ) / page_num ) * page_num ) + 1;
	const e_page = Math.min( total_page, ( s_page + page_num - 1 ) );
	const p_page = ( s_page > 1 ? ( s_page - 1 ) : 0 );
	const n_page = ( ( e_page < total_page ) ? ( e_page + 1 ) : 0 );

	const pages = [];
	if(p_page) {
		pages.push({type: 'prev',value: p_page});
	}
	for(var p=s_page; p<=e_page; p++) {
		pages.push({type: (p == page ? 'current' : 'page'),value: p});
	}
	if(n_page) {
		pages.push({type: 'next',value: n_page});
	}

	return pages;
}

export function showSearching() {
	jQuery('.is-searching').show();
	var winWidth = jQuery(window).width();
	var winHeight = jQuery(window).height();
	var obj = jQuery('.is-searching .progress-spinner');
	var w = parseInt( ( winWidth - 46 ) / 2 );
	var h = parseInt( ( winHeight - 46 ) / 2 );
	obj.css({
		'margin-top': h+'px',
		'margin-left': w+'px'
	});
}

export function hideSearching() {
	jQuery('.is-searching').hide();
}
