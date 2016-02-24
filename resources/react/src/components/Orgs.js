import React from 'react';
import Org from './Org';

const Orgs = ({
  orgs,
}) => {
  const rows = orgs.map((org) => {
    return (
      <Org key={org.orgId} {...org} />
    );
  });
  return (
    <div className="orgs">
      <h3>조직 검색 결과</h3>
      {rows}
    </div>
  );
};

export default Orgs;
