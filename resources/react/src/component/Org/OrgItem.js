import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Orgv from "./Orgv";
import OrgAgreement from "./OrgAgreement";

import { Link } from 'react-router';

export default class OrgItem extends Component{
  constructor(props) {
    super(props);
    this.state = {
      org: props.org,
    };
  }

  render() {
	const agreement_id = `agreements-${this.state.org.oid}`;
    const agreement_props = {
	  oid: this.state.org.oid,
	  nid: this.state.org.nid,
    };
	const a_button = (this.state.org.nid.length > 0 ? <span className="organ-agreement" onClick={this.onClickAgreement.bind(this)}>단체협약 보기</span> : '');
    return (
      <li key={this.state.org.oid} className="organize-item">
        <div className="header">
          <div className="title">
            <div className="organ-name">{this.state.org.fullname}</div>
            <div className="organ-summary">
			  <span className="organ-detail" onClick={this.onClickOrganize.bind(this)}>조직정보 보기</span>
			  {a_button}
            </div>
          </div>
        </div>
        <OrgAgreement key={agreement_id} {...agreement_props} />
      </li>
    );
  }

  onClickOrganize() {
    const type = 'overlay';
    const orgv_props = {
      org: this.state.org,
      type: type,
    };
    ReactDOM.render(<Orgv key={`organize-overlay-${this.state.org.oid}`} {...orgv_props} />,document.getElementById('overlay-container'));
  }

  onClickAgreement() {
  	jQuery(ReactDOM.findDOMNode(this)).find('.organ-agreement').toggleClass('activate');
  	jQuery(ReactDOM.findDOMNode(this)).find('.agreements dl.agreements-items').toggleClass('collapsed').slideToggle();
  }
};
