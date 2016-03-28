import React from 'react';
import { inQuery } from '../../util/utils';

const SelectOptions = ({
  query, field, name, options, oClick, onSClick,
}) => {
  const sname = `${field}-select`;
  const rows = options.map(({ name, value }) => {
    const id = `${field}-${value}`;
    const fname = `${field}`;
    const checked = inQuery(query, field, value);

    return (
      <li key={id} className="checkbox-wrap">
        <input type="radio" name={fname} id={id} checked={checked}
          onChange={() => { oClick(field, value); }}
        />
        <label className="checkbox" htmlFor={id}>
          <i className="unchecked fa fa-square-o"></i>
          <i className="checked fa fa-check-square"></i>
        </label>
        {' '}
        <label className="label" htmlFor={id}>{name}</label>
      </li>
    );
  });

  return (
    <div className="select-options">
      <div className="radio-button">
        <input type="radio" id={sname}
          onChange={() => { onSClick(sname); }}
        />
        <label htmlFor={sname}>
          <span>{name}</span>
		  <i className="fa fa-angle-down"></i>
        </label>
      </div>
      <ul id={sname} className="selectElement shadow">
        {rows}
      </ul>
    </div>
  );
};

export default SelectOptions;
