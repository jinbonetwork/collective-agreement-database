import React from 'react';
import { inQuery } from '../../util/utils';

const CheckboxOptions = ({
  query, field, name, options, classname, onClick,
}) => {
  const ulClassName = `${classname}`;
  const rows = options.map(({ name, value }) => {
    const id = `${field}-${value}`;
    const checked = inQuery(query, field, value);

    return (
      <li key={id} className="checkbox-wrap">
        <input type="checkbox" id={id} checked={checked}
          onChange={() => { onClick(field, value); }}
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
    <div className="checkbox-options top-dotted">
      <p>{name}</p>
      <ul className={ulClassName}>
        {rows}
      </ul>
    </div>
  );
};

export default CheckboxOptions;
