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
      {rows}
    </div>
  );
};

export default StandardList;

function makeItem(item) {
  const { id, subject, content } = item;

  const props = {
    sid: id,
    subject, content,
  };
  return <StandardItem key={id} {...props} />;
}
