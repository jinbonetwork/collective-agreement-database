import React, { Component } from 'react';
import axios from 'axios';

export default class Org extends Component {
  constructor() {
    super();
    this.state = {
      org: {},
    };
  }

  render() {
    return (
      <div className="row">
        Org {this.props.params.oid} <br />
        상급1 | 상급2 <br />
        단체협역 보기 | 다운받기 <br />
        <br />
        총연합단체: {getNames(this.state.org.f1)} <br />
        산별연맹: {getNames(this.state.org.f2)} <br />
        업종조직: {getNames(this.state.org.f3)} <br />
        지역: {getNames(this.state.org.f4)} <br />
        복수노조: {getNames(this.state.org.f5)} <br />
        과반노조: {getNames(this.state.org.f6)} <br />
        조합원수: {this.state.org.f7} <br />
        사업자명(원청): {this.state.org.f8} <br />
        특성: {getNames(this.state.org.f10)} <br />
        고용형태: {getNames(this.state.org.f11)} <br />
        산업/직종: {getNames(this.state.org.f12)} <br />
        부처 : {getNames(this.state.org.f13)} <br />
        <br />
        사업장 정보<br />
        대표자명: {this.state.org.f14} <br />
        전화: {this.state.org.f15} <br />
        주소: {this.state.org.f16} <br />
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
