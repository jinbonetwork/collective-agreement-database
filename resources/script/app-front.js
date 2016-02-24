// revised version: 2016-01-31

let fakeResult = {
	orgs: [
		{ key: 'adadfa', text: '조직1' },
		{ key: 'geadfa', text: '조직2' },
		{ key: 'qegadd', text: '조직3' },
		{ key: 'gqesdf', text: '조직4' },
		{ key: 'qasfes', text: '조직5' },
		{ key: 'geafes', text: '조직6' },
		{ key: 'agesdd', text: '조직7' }
	],
	articles: [
		{ key: 'adadfa', text: '단체협약 1' },
		{ key: 'geadfa', text: '단체협약 2' },
		{ key: 'qegadd', text: '단체협약 3' },
		{ key: 'gqesdf', text: '단체협약 4' },
		{ key: 'qasfes', text: '단체협약 5' },
		{ key: 'geafes', text: '단체협약 6' },
		{ key: 'agesdd', text: '단체협약 7' }
	]
};

class SearchBox extends React.Component {
	constructor() {
		super();
		this.state = {
			result: { orgs: [], articles: [] }
		}
	}
	render() {
		return (
			<div className="searchBox">
				<SearchForm onSubmit={this.doSearch.bind(this)} />
				<SearchResult result={this.state.result} />
			</div>
		);
	}
	doSearch(query) {
		let result = this.getSearchResult(query);
		this.setState({ result: result });
	}
	getSearchResult(query) {
		let orgs = fakeResult.orgs.map(item =>
			{ return { key: item.key, text: query + item.text }; });
		let articles = fakeResult.orgs.map(item =>
			{ return { key: item.key, text: query + item.text }; });
		return { orgs, articles };
	}
}

class SearchForm extends React.Component {
	constructor() {
		super();
		this.state = { query: '' };
	}
	handelQueryChange(e) {
		this.setState({ query: e.target.value });
	}
	handelSubmit(e) {
		e.preventDefault();
		let query = this.state.query.trim();
		if (query) {
			this.props.onSubmit(query)
		}
	}
	render() {
		return (
			<div className="searchForm">
				<h3> Search </h3>
				<form onSubmit={this.handelSubmit.bind(this)}>
					<input
						type="text"
						value={this.state.query}
						onChange={this.handelQueryChange.bind(this)}
					/>
					<input
						type="submit"
						value="Search"
					/>
				</form>
			</div>
		);
	}
}

class SearchResult extends React.Component {
	render() {
		return (
			<div className="searchResult">
				<OrgList orgs={this.props.result.orgs} />
				<ArticleList articles={this.props.result.articles} />
			</div>
		);
	}
}

class OrgList extends React.Component {
	render() {
		let nodes = this.props.orgs.map((org) =>
			<Org key={org.key} text={org.text} />);
		return (
			<div className="orgList">
				<h3> Orgs </h3>
				{nodes}
			</div>
		);
	}
}

class Org extends React.Component {
	render() {
		return (
			<div>
				<a href="#"><span>{this.props.key}</span></a>
				-
				<span>{this.props.text}</span>
			</div>
		)
	}
}

class ArticleList extends React.Component {
	render() {
		let nodes = this.props.articles.map((article) =>
			<Article key={article.key} text={article.text} />);
		return (
			<div className="articleList">
				<h3> Articles </h3>
				{nodes}
			</div>
		);
	}
}

class Article extends React.Component {
	render() {
		return (
			<div>
				<a href="#"><span>{this.props.key}</span></a>
				-
				<span>{this.props.text}</span>
			</div>
		)
	}
}

ReactDOM.render(
	<SearchBox />,
	document.getElementById('app')
);

