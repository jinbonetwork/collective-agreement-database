import React from 'react';
import StandardItem from './StandardItem';

const StandardList = ({
  standards
}) => {
  let items = standards.map(makeItem);
  items.length = 5;
  const rows = items.length ? <ul> {items} </ul>
             : <div className="no-result">검색 결과가 없습니다.</div>;

  return (
    <div className="example-result">
      <div className="header">모범단협 검색결과</div>
      {rows}
    </div>
  );
};

export default StandardList;

function makeItem(item) {
  const { id, subject, content } = item;

  const props = {
    standard: item,
  };
  return <StandardItem key={`standard-${id}`} {...props} />;
}
