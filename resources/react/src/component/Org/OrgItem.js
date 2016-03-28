import React from 'react';
import { Link } from 'react-router';

// TODO : how to get agreement id
const OrgItem = ({
  oid, fullname, nid
}) => {
  return (
    <li key={oid}>
      <div className="header">
        <div className="title">
          <div className="organ-name">{fullname}</div>
          <Link to={`/orgs/${oid}`}>조직정보 보기</Link>{' '}
          {nid ? <Link to={`/articles/${nid}`}>전문보기</Link> : ''}
        </div>
      </div>
    </li>
  );
};

export default OrgItem;
