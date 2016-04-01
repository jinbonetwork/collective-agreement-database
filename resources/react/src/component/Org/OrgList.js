import React from 'react';
import OrgItem from './OrgItem';
import { Link } from 'react-router';

const OrgList = ({
  result, orgs
}) => {
  const items = orgs.map(makeItem);
  const rows = items.length ? <ul> {items} </ul>
             : <div className="no-result">검색 결과가 없습니다.</div>;
  const more = result.orgs && result.orgs.more || 0;
  const moreClass = ( more ? 'more-box show' : 'more-box hide');
  const query = window.location.search;
  const totalCount = result.orgs && result.orgs.total_cnt || 0;

  return (
    <div className="organization-result">
      <div className="header">조직 검색 결과 :: {' '}
        <span className="result-counts">{totalCount}</span>개
      </div>
      {rows}
	  <div className={moreClass}><Link to={`/orgs${query}`}><span>더보기</span></Link></div>
    </div>
  );
};

export default OrgList;

function makeItem(org) {
  const { oid, fullname, nid } = org;

  const props = {
    org: org,
  };
  const id = `organize-${oid}`;
  return <OrgItem key={id} {...props} />;
}
