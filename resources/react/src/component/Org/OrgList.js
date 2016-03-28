import React from 'react';
import OrgItem from './OrgItem';

const OrgList = ({
  result, orgs
}) => {
  const items = orgs.map(makeItem);
  const rows = items.length ? <ul> {items} </ul>
             : <div className="no-result">검색 결과가 없습니다.</div>;
  const totalCount = result.orgs && result.orgs.total_cnt || 0;

  return (
    <div className="organization-result">
      <div className="header">조직 검색 결과 :: {' '}
        <span className="result-counts">{totalCount}</span>개
      </div>
      {rows}
    </div>
  );
};

export default OrgList;

function makeItem(org) {
  // TODO: how to get nid
  const { oid, fullname, nid } = org;

  const props = {
    oid, fullname, nid
  };
  return <OrgItem key={oid} {...props} />;
}
