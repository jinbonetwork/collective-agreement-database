import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router';

export default class Org extends Component {
  constructor() {
    super();
    this.state = {
      org: {},
    };
  }

  render() {
    return (
      <div className="organ-info-container">
	    <div className="orgain-info">
		  <div className="header">
		    <div className="organ-name">
			  <span className="lowset-level">{this.state.org.fullname}</span>
			</div>
			<div className="agreement">
        	  <a className="agree-view"><span>단체협역 보기</span></a>
			  <a className="agree-download"><span>다운받기</span></a>
            </div>
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
              <div className="row">특성</div>
              <div className="row">고용형태</div>
              <div className="row">산업/직종</div>
              <div className="row">부처</div>
            </div>
		    <div className="column info">
              <div className="row">{getNames(this.state.org.f1)}</div>
              <div className="row">{getNames(this.state.org.f2)}</div>
              <div className="row">{getNames(this.state.org.f3)}</div>
              <div className="row">{getNames(this.state.org.f4)}</div>
              <div className="row">{getNames(this.state.org.f5)}</div>
              <div className="row">{getNames(this.state.org.f6)}</div>
              <div className="row">{this.state.org.f7}</div>
              <div className="row">{this.state.org.f8}</div>
              <div className="row">{getNames(this.state.org.f10)}</div>
              <div className="row">{getNames(this.state.org.f11)}</div>
              <div className="row">{getNames(this.state.org.f12)}</div>
              <div className="row">{getNames(this.state.org.f13)}</div>
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
		</div>
      </div>
    );
  }

  componentWillMount() {
    console.log('- Org componentWillMount');
    this.doSearch();
  }

  doSearch() {
    const api = '/api/orgs';
    const oid = this.props.params.oid;
    const url = `${api}/${oid}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin
	  console.log(data.orgs);

      this.setState({
        org: data.orgs,
      });
    });
  }
}

function getNames(arr) {
  return arr ? arr.reduce((acc, v) => {
    return acc ? `${acc}, ${v.name}` : v.name;
  }, '') : '-';
}

function makeOrgName(orgs) {
  const { oid ,name } = orgs;

  if(oid) {
  	return <Link to={`/orgs/${oid}`}><span className="higher-level">{name}</span></Link>;
  } else {
    return <span className="lowset-level">{name}</span>;
  }
}
