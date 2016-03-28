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
