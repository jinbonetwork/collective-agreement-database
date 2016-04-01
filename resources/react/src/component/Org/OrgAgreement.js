import React, { Component } from 'react';
import { Link } from 'react-router';

export default class OrgAgreement extends Component{
  constructor(props) {
    super(props);
    this.state = {
      oid: props.oid,
      nid: props.nid,
      click: false,
    };
  }

  render() {
    const agreement_id = `agreements-${this.state.oid}`;
    const cName = (this.state.click ? "" : "collapsed");
    let items = ( Array.isArray(this.state.nid) ? this.state.nid.map(makeItem) : [] );
    if(items.length > 0) {
      return (
        <div key={`organize-agreement-${this.state.oid}-${agreement_id}`} className="agreements">
          <dl className={`agreements-items ${cName}`}>
            <dt onClick={this.handleAgreementClick.bind(this)}><span>단체협약보기</span></dt>
            <dd>
              <ul>{items}</ul>
            </dd>
          </dl>
        </div>
      );
    } else {
      return <div className="hidden" />;
    }
  }

  handleAgreementClick() {
  	this.setState({
      click: !this.state.click
    });
  }
}

function makeItem(nids) {
  const { nid, did, subject } = nids;

  return (
    <li key={`organize-agreement-${nid}`} className="agreement">
      <Link to={`/articles/${nid}`} className="agree-view"><span>{subject}</span></Link>
	  <div className="download">
        <a href={`/articles/pdf?nid=${nid}`} className="pdf-download"><span>다운받기</span></a>
      </div>
    </li>
  );
}
