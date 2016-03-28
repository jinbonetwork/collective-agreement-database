import React from 'react';
import { getLabel } from '../../util/utils';

const QueryLabelList = ({
  query, labels, onClick,
}) => {
  const queryLabels = [];
  for (const field in query) {
    const values = query[field];
    values.forEach((value) => {
      queryLabels.push({
        field,
        value,
        label: getLabel(labels, field, value)
      });
    });
  }

  const rows = queryLabels.map(({ field, value, label }) => {
    return (
      <div key={`${field}-${value}`} className="key-list gen-key">
        <i className="header fa fa-key"></i>
        {label}
        <i className="close fa fa-times"
          onClick={() => { onClick(field, value); }}
        ></i>
      </div>
    );
  });

  return (
    <div>{rows}</div>
  );
};

export default QueryLabelList;
