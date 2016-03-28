import React from 'react';
import SelectOptions from './SelectOptions';
import CheckboxOptions from './CheckboxOptions';

const OrgCategory = ({
  query, catetories, onCheckboxClick, onSelectSelect, onSelectClick,
}) => {
  if (catetories.length === 0) {
    return <div />;
  }

  return (
    <div className="organization">
      <div className="width33">
        <SelectOptions
          {...catetories[0]}
          query={query} field={catetories[0].key}
          oClick={onSelectSelect}
		  onSClick={onSelectClick}
        />
        <SelectOptions
          {...catetories[1]}
          query={query} field={catetories[1].key}
          oClick={onSelectSelect}
		  onSClick={onSelectClick}
        />
        <SelectOptions
          {...catetories[2]}
          query={query} field={catetories[2].key}
          oClick={onSelectSelect}
		  onSClick={onSelectClick}
        />
        <SelectOptions
          {...catetories[3]}
          query={query} field={catetories[3].key}
          oClick={onSelectSelect}
		  onSClick={onSelectClick}
        />
        <SelectOptions
          {...catetories[4]}
          query={query} field={catetories[4].key}
          oClick={onSelectSelect}
		  onSClick={onSelectClick}
        />
        <CheckboxOptions
          {...catetories[8]}
          query={query} field={catetories[8].key}
          onClick={onCheckboxClick}
		  classname='column2'
        />
      </div>
      <div className="width33">
        <CheckboxOptions
          {...catetories[6]}
          query={query} field={catetories[6].key}
          onClick={onCheckboxClick}
		  classname='column1'
        />
        <CheckboxOptions
          {...catetories[9]}
          query={query} field={catetories[9].key}
          onClick={onCheckboxClick}
		  classname='column1'
        />
      </div>
      <div className="width33">
        <CheckboxOptions
          {...catetories[7]}
          query={query} field={catetories[7].key}
          onClick={onCheckboxClick}
		  classname='column2'
        />
        <CheckboxOptions
          {...catetories[10]}
          query={query} field={catetories[10].key}
          onClick={onCheckboxClick}
		  classname='column'
        />
      </div>
    </div>
  );
};

export default OrgCategory;
