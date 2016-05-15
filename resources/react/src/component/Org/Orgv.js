import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';
import { Link } from 'react-router';

import OrgAgreement from "./OrgAgreement";
import { showSearching, hideSearching } from '../../util/utils';

export default class Orgv extends Component{
  constructor(props) {
    super(props);
    this.state = {
      org: props.org,
      type: props.type,
    };
  }

  render() {
    const cname = `organ-info ${this.state.type}`;
    const rows = this.state.org.oid ? this.state.org.organizes.map(({oid,name}) => {
      const cname = oid ? 'higher-level' : 'lowset-level';
      return (
        <span key={oid} className={cname} onClick={() => this.onOrgClick(oid)}>{name}</span>
	  );
    }) : '';
    const closeButton = (this.state.type == 'overlay' ? <i className="close fa fa-close" onClick={this.onOrgClose.bind(this)}></i> : '');
    const back = (this.state.type == 'overlay' ? <div className="organ-background" onClick={this.onOrgClose.bind(this)}></div> : '');
    const agreement_id = `agreements-${this.state.org.oid}`;
    const agreement_props = {
      oid : this.state.org.oid,
      nid : this.state.org.nid,
    };
	const edit = ( this.state.org.owner ? <button onClick={this.onOrgEdit.bind(this)}>단체수정</button> : '' );

    if(this.state.org.oid) {
      return (
        <div key={agreement_id} id={agreement_id} className={cname}>
          <div className="organ-info-container">
            <div className="organ-info-box">
              <div className="organ-info-box-wrapper">
                <div className="organ-info">
                  <div className="header">
                    <div className="organ-name">
                      {rows}
                    </div>
                    <OrgAgreement key={agreement_id} {...agreement_props} />
                  </div>
                  <div className="content">
                    <div className="column label">
                      <div className="row">총연합단체</div>
                      <div className="row">산별연맹</div>
                      <div className="row">업종조직</div>
                      <div className="row">지역</div>
                      <div className="row">복수노조</div>
                      <div className="row">과반노조</div>
                      <div className="row">조합원수</div>
                      <div className="row">사업자명(원청)</div>
                      <div className="row">사업자명(하청)</div>
                      <div className="row">특성</div>
                      <div className="row">고용형태</div>
                      <div className="row">산업/직종</div>
                      <div className="row">부처</div>
                    </div>
                    <div className="column info">
                      <div className="row">{this.getNames(this.state.org.f1)}</div>
                      <div className="row">{this.getNames(this.state.org.f2)}</div>
                      <div className="row">{this.getNames(this.state.org.f3)}</div>
                      <div className="row">{this.getNames(this.state.org.f4)}</div>
                      <div className="row">{this.getNames(this.state.org.f5)}</div>
                      <div className="row">{this.getNames(this.state.org.f6)}</div>
                      <div className="row">{this.state.org.f7}</div>
                      <div className="row" dangerouslySetInnerHTML={{ __html: this.state.org.f8 }} />
                      <div className="row" dangerouslySetInnerHTML={{ __html: this.state.org.f9 }} />
                      <div className="row">{this.getNames(this.state.org.f10)}</div>
                      <div className="row">{this.getNames(this.state.org.f11)}</div>
                      <div className="row">{this.getNames(this.state.org.f12)}</div>
                      <div className="row">{this.getNames(this.state.org.f13)}</div>
                    </div>
                  </div>
                </div>
                <div className="company-info">
                  <div className="header">사업장 정보</div>
                  <div className="content">
                    <div className="column label">
                      <div className="row">대표자명</div>
                      <div className="row">전화</div>
                      <div className="row">주소</div>
                    </div>
                    <div className="column info">
                      <div className="row">{this.state.org.f14}</div>
                      <div className="row">{this.state.org.f15}</div>
                      <div className="row">{this.state.org.f16}</div>
                    </div>
                  </div>
                </div>
                <div className="footer">
					{edit}
                </div>
              </div>
			</div>
            {closeButton}
          </div>
          {back}
        </div>
      );
	} else {
      return <div className="hidden" />;
	}
  }

  componentDidMount() {
    var self = this;
    jQuery(window).bind('resize.orgv',function(e) {
      self.handleResize();
    });
    if(this.state.type == 'overlay') {
      this.overlayResize();
    }
	var b = jQuery(ReactDOM.findDOMNode(this)).find('.organ-background');
	if(b.length > 0) {
      jQuery(window).bind('keydown.orgv',function(event) {
        var code = event.charCode || event.keyCode;
        if(code == 27) {
          self.onOrgClose();
        }
      });
    }
  }

  componentDidUpdate() {
    this.overlayResize();
  }

  componentWillUnmount() {
	jQuery(window).unbind('resize.orgv');
	jQuery(window).unbind('keydown.orgv');
  }

  handleResize() {
    this.overlayResize();
  }

  overlayResize() {
    if(this.state.type == 'overlay') {
      var winWidth = window.innerWidth;
      var winHeight = window.innerHeight;
      var obj = jQuery(ReactDOM.findDOMNode(this)).find('.organ-info-container');
      var w = Math.min(600, parseInt( winWidth * 0.9 ) );
      var max_h = parseInt( winHeight * 0.9 );
      obj.css({
        'width' : w+'px',
        'height' : max_h+'px',
        'left' : parseInt( ( winWidth - w ) / 2 )+'px',
        'top' : parseInt( ( winHeight - max_h ) / 2)+'px'
      });
    }
  }

  onOrgClick(oid) {
    if(this.state.type == 'overlay') {
      this.doSearch(oid);
    } else {
      if(oid) {
        window.location = `/orgs/${oid}`;
      }
    }
  }

  onOrgEdit() {
  	window.location = '/orgs/edit?oid='+this.state.org.oid;
  }

  onOrgClose() {
    ReactDOM.unmountComponentAtNode(document.getElementById('overlay-container'));
  }

  doSearch(oid) {
    const api = '/api/orgs';
    const url = `${api}/${oid}`;

    howSearching();
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      this.setState({
        org: data.orgs,
      });
    });
  }

  getNames(arr) {
    return arr ? arr.reduce((acc, v) => {
      return acc ? `${acc}, ${v.name}` : v.name;
    }, '') : '-';
  }
}
